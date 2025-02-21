<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanCrosses
{
    public function __invoke(Geometry $feature1, Geometry $feature2): bool
    {
        if ($feature1 instanceof MultiPoint && $feature2 instanceof LineString) {
            return self::doMultiPointAndLineStringCross($feature1, $feature2);
        }

        if ($feature1 instanceof MultiPoint && $feature2 instanceof Polygon) {
            return self::doesMultiPointCrossPoly($feature1, $feature2);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof MultiPoint) {
            return self::doMultiPointAndLineStringCross($feature2, $feature1);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof LineString) {
            return Turf::booleanIntersect($feature1, $feature2);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof Polygon) {
            return Turf::booleanIntersect($feature1, $feature2);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof MultiPoint) {
            return self::doesMultiPointCrossPoly($feature2, $feature1);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof LineString) {
            return Turf::booleanIntersect($feature2, $feature1);
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
}
