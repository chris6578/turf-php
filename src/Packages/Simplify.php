<?php

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class Simplify
{
    public function __invoke(
        GeoJson $geojson,
        float $tolerance = 1.0,
        bool $highQuality = false
    ): Feature|FeatureCollection|GeometryCollection|GeoJson {
        if ($tolerance < 0) {
            throw new InvalidArgumentException('Invalid tolerance value.');
        }

        if ($geojson instanceof FeatureCollection) {
            $features = array_map(fn ($feature) => self::simplifyFeature($feature, $tolerance, $highQuality)->getGeometry(), $geojson->getFeatures());

            return new FeatureCollection($features);
        } elseif ($geojson instanceof GeometryCollection) {
            $geometries = array_map(fn ($geometry) => self::simplifyFeature($geometry, $tolerance, $highQuality)->getGeometry(), $geojson->getGeometries());

            return new GeometryCollection($geometries);
        } else {
            return self::simplifyFeature($geojson, $tolerance, $highQuality);
        }
    }

    /**
     * Simplifies a Feature or Geometry.
     *
     * @param  GeoJson  $geojson  Feature or Geometry object.
     * @param  float  $tolerance  The simplification tolerance.
     * @param  bool  $highQuality  If true, uses a high-quality simplification algorithm.
     * @return GeoJson The simplified Feature or Geometry.
     */
    private static function simplifyFeature(
        GeoJson $geojson,
        float $tolerance,
        bool $highQuality
    ): Feature|LineString|MultiLineString|Polygon|MultiPolygon|GeoJson {
        if ($geojson instanceof Feature) {
            return new Feature(self::simplifyFeature($geojson->getGeometry(), $tolerance, $highQuality)->getGeometry());
        }

        switch (get_class($geojson)) {
            case LineString::class:
                return new LineString(self::simplifyLineString($geojson->getCoordinates(), $tolerance, $highQuality));
            case MultiLineString::class:
                return new MultiLineString(array_map(fn ($coords) => self::simplifyLineString($coords, $tolerance, $highQuality), $geojson->getCoordinates()));
            case Polygon::class:
                return new Polygon(self::simplifyPolygon($geojson->getCoordinates(), $tolerance, $highQuality));
            case MultiPolygon::class:
                return new MultiPolygon(array_map(fn ($coords) => self::simplifyPolygon($coords, $tolerance, $highQuality), $geojson->getCoordinates()));
            default:
                return $geojson; // Return as-is for unsupported types
        }
    }

    /**
     * Simplifies a LineString using Ramer-Douglas-Peucker algorithm.
     *
     * @param  mixed[]  $coords  LineString coordinates.
     * @param  float  $tolerance  The simplification tolerance.
     * @param  bool  $highQuality  If true, uses a high-quality simplification algorithm.
     * @return mixed[] Simplified coordinates.
     */
    private static function simplifyLineString(
        array $coords,
        float $tolerance,
        bool $highQuality
    ): array {
        return self::ramerDouglasPeucker($coords, $tolerance);
    }

    /**
     * Simplifies a Polygon while keeping it valid.
     *
     * @param  mixed[]  $coords  Polygon coordinates.
     * @param  float  $tolerance  The simplification tolerance.
     * @param  bool  $highQuality  If true, uses a high-quality simplification algorithm.
     * @return mixed[] Simplified polygon coordinates.
     */
    private static function simplifyPolygon(
        array $coords,
        float $tolerance,
        bool $highQuality
    ): array {

        return array_map(function ($ring) use ($tolerance) {
            if (count($ring) < 4) {
                throw new InvalidArgumentException('Invalid polygon: fewer than 4 points.');
            }

            $simplifiedRing = self::ramerDouglasPeucker($ring, $tolerance);

            // Ensure valid polygon (must have at least 3 distinct points)
            while (! self::checkPolygonValidity($simplifiedRing)) {
                $tolerance *= 0.99; // Reduce tolerance slightly
                $simplifiedRing = self::ramerDouglasPeucker($ring, $tolerance);
            }

            // Ensure ring is closed
            if ($simplifiedRing[0] !== end($simplifiedRing)) {
                $simplifiedRing[] = $simplifiedRing[0];
            }

            return $simplifiedRing;
        }, $coords);
    }

    /**
     * Checks if a Polygon ring is valid (must have at least 3 unique points).
     *
     * @param  mixed[]  $ring  Polygon ring coordinates.
     * @return bool True if valid, false otherwise.
     */
    private static function checkPolygonValidity(array $ring): bool
    {
        return count($ring) >= 3 && ($ring[0] !== $ring[count($ring) - 1] || count(array_unique($ring, SORT_REGULAR)) > 2);
    }

    /**
     * Ramer-Douglas-Peucker algorithm for simplifying a set of points.
     *
     * @param  array<float[]>  $points  The list of points.
     * @param  float  $epsilon  The simplification tolerance.
     * @return mixed[] The simplified list of points.
     */
    private static function ramerDouglasPeucker(array $points, float $epsilon): array
    {
        if (count($points) < 3) {
            return $points;
        }

        $dmax = 0;
        $index = 0;
        $end = count($points) - 1;

        for ($i = 1; $i < $end; $i++) {
            $d = self::perpendicularDistance($points[$i], $points[0], $points[$end]);
            if ($d > $dmax) {
                $index = $i;
                $dmax = $d;
            }
        }

        if ($dmax > $epsilon) {
            $recResults1 = self::ramerDouglasPeucker(array_slice($points, 0, $index + 1), $epsilon);
            $recResults2 = self::ramerDouglasPeucker(array_slice($points, $index, $end - $index + 1), $epsilon);

            return array_merge(array_slice($recResults1, 0, -1), $recResults2);
        } else {
            return [$points[0], $points[$end]];
        }
    }

    /**
     * Calculates the perpendicular distance from a point to a line.
     *
     * @param  float[]  $point  The point [x, y].
     * @param  float[]  $lineStart  The start of the line [x, y].
     * @param  float[]  $lineEnd  The end of the line [x, y].
     * @return float The perpendicular distance.
     */
    private static function perpendicularDistance(array $point, array $lineStart, array $lineEnd): float
    {
        [$px, $py] = $point;
        [$x1, $y1] = $lineStart;
        [$x2, $y2] = $lineEnd;

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        $mag = $dx * $dx + $dy * $dy;

        if ($mag == 0) {
            return sqrt(($px - $x1) ** 2 + ($py - $y1) ** 2);
        }

        $t = (($px - $x1) * $dx + ($py - $y1) * $dy) / $mag;

        if ($t < 0) {
            return sqrt(($px - $x1) ** 2 + ($py - $y1) ** 2);
        } elseif ($t > 1) {
            return sqrt(($px - $x2) ** 2 + ($py - $y2) ** 2);
        }

        $x = $x1 + $t * $dx;
        $y = $y1 + $t * $dy;

        return sqrt(($px - $x) ** 2 + ($py - $y) ** 2);
    }
}
