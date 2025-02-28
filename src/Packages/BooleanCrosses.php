<?php

declare(strict_types=1);

namespace Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use Turf\Turf;

class BooleanCrosses
{
    public function __invoke(Geometry $feature1, Geometry $feature2): bool
    {
        if ($feature1 instanceof LineString && $feature2 instanceof LineString) {
            return $this->doLineStringsCross($feature1, $feature2);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof Polygon) {
            return $this->doesLineStringCrossPolygon($feature1, $feature2);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof LineString) {
            return $this->doesLineStringCrossPolygon($feature2, $feature1);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof MultiPoint) {
            return $this->doesMultiPointCrossPoly($feature2, $feature1);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof MultiPoint) {
            return $this->doMultiPointAndLineStringCross($feature2, $feature1);
        }

        if ($feature1 instanceof MultiPoint && $feature2 instanceof Polygon) {
            return $this->doesMultiPointCrossPoly($feature1, $feature2);
        }

        if ($feature1 instanceof MultiPoint && $feature2 instanceof LineString) {
            return $this->doMultiPointAndLineStringCross($feature1, $feature2);
        }

        throw new InvalidArgumentException('Unsupported geometry types');
    }

    public function doMultiPointAndLineStringCross(MultiPoint $multiPoint, LineString $lineString): bool
    {
        $foundIntPoint = false;
        $foundExtPoint = false;
        foreach ($multiPoint->getCoordinates() as $point) {
            if (Turf::booleanPointOnLine(new Point($point), $lineString)) {
                $foundIntPoint = true;
            } else {
                $foundExtPoint = true;
            }
        }

        return $foundIntPoint && $foundExtPoint;
    }

    public function doesMultiPointCrossPoly(MultiPoint $multiPoint, Polygon $polygon): bool
    {
        $foundIntPoint = false;
        $foundExtPoint = false;
        foreach ($multiPoint->getCoordinates() as $point) {
            if (Turf::booleanPointInPolygon(new Point($point), $polygon)) {
                $foundIntPoint = true;
            } else {
                $foundExtPoint = true;
            }
        }

        return $foundIntPoint && $foundExtPoint;
    }

    public function doLineStringsCross(LineString $line1, LineString $line2): bool
    {
        $intersections = $this->findIntersections($line1, $line2);
        foreach ($intersections as $intersection) {
            if (! $this->isEndpoint($intersection, $line1) && ! $this->isEndpoint($intersection, $line2)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed[]
     */
    private function findIntersections(LineString $line1, LineString $line2): array
    {
        $intersections = [];
        $coords1 = $line1->getCoordinates();
        $coords2 = $line2->getCoordinates();
        for ($i = 0; $i < count($coords1) - 1; $i++) {
            for ($j = 0; $j < count($coords2) - 1; $j++) {
                $intersection = $this->lineSegmentIntersection(
                    $coords1[$i], $coords1[$i + 1],
                    $coords2[$j], $coords2[$j + 1]
                );
                if ($intersection) {
                    $intersections[] = $intersection;
                }
            }
        }

        return $intersections;
    }

    /**
     * @param  float[]  $p1
     * @param  float[]  $p2
     * @param  float[]  $p3
     * @param  float[]  $p4
     * @return float[]|null
     */
    private function lineSegmentIntersection(array $p1, array $p2, array $p3, array $p4): ?array
    {
        $denominator = ($p4[1] - $p3[1]) * ($p2[0] - $p1[0]) - ($p4[0] - $p3[0]) * ($p2[1] - $p1[1]);
        if ($denominator == 0) {
            return null; // Parallel or coincident lines
        }

        $ua = (($p4[0] - $p3[0]) * ($p1[1] - $p3[1]) - ($p4[1] - $p3[1]) * ($p1[0] - $p3[0])) / $denominator;
        $ub = (($p2[0] - $p1[0]) * ($p1[1] - $p3[1]) - ($p2[1] - $p1[1]) * ($p1[0] - $p3[0])) / $denominator;

        if ($ua >= 0 && $ua <= 1 && $ub >= 0 && $ub <= 1) {
            return [
                $p1[0] + $ua * ($p2[0] - $p1[0]),
                $p1[1] + $ua * ($p2[1] - $p1[1]),
            ];
        }

        return null;
    }

    /**
     * @param  float[]  $point
     */
    private function isEndpoint(array $point, LineString $line): bool
    {
        $coords = $line->getCoordinates();

        return $point == $coords[0] || $point == end($coords);
    }

    public function doesLineStringCrossPolygon(LineString $line, Polygon $polygon): bool
    {
        $boundary = new LineString($polygon->getCoordinates()[0]);
        if ($this->doLineStringsCross($line, $boundary)) {
            $startPoint = new Point($line->getCoordinates()[0]);
            $endPoint = new Point($line->getCoordinates()[count($line->getCoordinates()) - 1]);
            if (! Turf::booleanPointInPolygon($startPoint, $polygon) || ! Turf::booleanPointInPolygon($endPoint, $polygon)) {
                return true;
            }
        }

        return false;
    }
}
