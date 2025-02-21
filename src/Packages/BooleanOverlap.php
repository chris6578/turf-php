<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanOverlap
{
    public function __invoke(Geometry $geometry1, Geometry $geometry2): bool
    {
        if (get_class($geometry1) !== get_class($geometry2)) {
            throw new InvalidArgumentException('Features must be of the same type');
        }

        if ($geometry1 instanceof MultiPoint && $geometry2 instanceof MultiPoint) {
            return self::doMultiPointsOverlap($geometry1, $geometry2);
        }

        if ($geometry1 instanceof LineString && $geometry2 instanceof LineString) {
            return self::doLineStringsOverlap($geometry1, $geometry2);
        }

        if ($geometry1 instanceof Polygon && $geometry2 instanceof Polygon) {
            return self::doPolygonsOverlap($geometry1, $geometry2);
        }

        throw new InvalidArgumentException('Unsupported geometry type');
    }

    public function doMultiPointsOverlap(MultiPoint $multiPoint1, MultiPoint $multiPoint2): bool
    {
        foreach ($multiPoint1->getCoordinates() as $coord1) {
            foreach ($multiPoint2->getCoordinates() as $coord2) {
                if ($coord1[0] === $coord2[0] && $coord1[1] === $coord2[1]) {
                    return true;
                }
            }
        }

        return false;
    }

    public function doLineStringsOverlap(LineString $lineString1, LineString $lineString2): bool
    {
        return Turf::booleanIntersect($lineString1, $lineString2);
    }

    public function doPolygonsOverlap(Polygon $polygon1, Polygon $polygon2): bool
    {
        return Turf::booleanIntersect($polygon1, $polygon2);
    }
}
