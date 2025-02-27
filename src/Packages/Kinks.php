<?php

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class Kinks
{
    public function __invoke(
        GeoJson $featureIn
    ): FeatureCollection {
        $coordinates = [];
        $features = [];

        // Extract the geometry from the feature
        $feature = ($featureIn instanceof Feature) ? $featureIn->getGeometry() : $featureIn;

        if ($feature instanceof LineString) {
            $coordinates = [$feature->getCoordinates()];
        } elseif ($feature instanceof MultiLineString) {
            $coordinates = $feature->getCoordinates();
        } elseif ($feature instanceof MultiPolygon) {
            foreach ($feature->getCoordinates() as $polygon) {
                $coordinates = array_merge($coordinates, $polygon);
            }
        } elseif ($feature instanceof Polygon) {
            $coordinates = $feature->getCoordinates();
        } else {
            throw new InvalidArgumentException('Input must be a LineString, MultiLineString, Polygon, or MultiPolygon Feature or Geometry.');
        }

        // Compare each segment in the geometry for intersections
        foreach ($coordinates as $line1) {
            foreach ($coordinates as $line2) {
                for ($i = 0; $i < count($line1) - 1; $i++) {
                    for ($k = $i; $k < count($line2) - 1; $k++) {
                        if ($line1 === $line2) {
                            // Skip adjacent segments in the same line
                            if (abs($i - $k) === 1) {
                                continue;
                            }
                            // Skip first and last segment in closed Polygon or LineString
                            if ($i === 0 && $k === count($line1) - 2 && $line1[$i] === $line1[$k + 1]) {
                                continue;
                            }
                        }

                        $intersection = self::lineIntersects(
                            $line1[$i][0], $line1[$i][1], $line1[$i + 1][0], $line1[$i + 1][1],
                            $line2[$k][0], $line2[$k][1], $line2[$k + 1][0], $line2[$k + 1][1]
                        );

                        if ($intersection) {
                            $features[] = new Feature(new Point($intersection));
                        }
                    }
                }
            }
        }

        return new FeatureCollection($features);
    }

    /**
     * @param float|int $x1
     * @param float|int $y1
     * @param float|int $x2
     * @param float|int $y2
     * @param float|int $x3
     * @param float|int $y3
     * @param float|int $x4
     * @param float|int $y4
     * @return float[]|int[]|null
     */
    private static function lineIntersects($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4): ?array
    {
        $denominator = ($y4 - $y3) * ($x2 - $x1) - ($x4 - $x3) * ($y2 - $y1);
        if ($denominator == 0) {
            return null; // Parallel or coincident lines
        }

        $a = $y1 - $y3;
        $b = $x1 - $x3;
        $numerator1 = ($x4 - $x3) * $a - ($y4 - $y3) * $b;
        $numerator2 = ($x2 - $x1) * $a - ($y2 - $y1) * $b;
        $a = $numerator1 / $denominator;
        $b = $numerator2 / $denominator;

        if ($a >= 0 && $a <= 1 && $b >= 0 && $b <= 1) {
            return [
                $x1 + $a * ($x2 - $x1),
                $y1 + $a * ($y2 - $y1),
            ];
        }

        return null;
    }
}
