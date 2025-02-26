<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanContains
{
    public function __invoke(Geometry $geometry1, Geometry $geometry2): bool
    {
        if ($geometry1 instanceof LineString) {
            return $geometry2 instanceof Point ? Turf::booleanPointOnLine($geometry2, $geometry1) : false;
        }

        if ($geometry1 instanceof Point) {
            return $geometry2 instanceof Point && Helpers::compareCoords($geometry1->getCoordinates(), $geometry2->getCoordinates());
        }

        if ($geometry1 instanceof MultiPoint) {
            return $geometry2 instanceof Point ? self::isPointInMultiPoint($geometry1, $geometry2) : false;
        }

        if ($geometry1 instanceof Polygon) {
            return match (true) {
                $geometry2 instanceof Point => Turf::booleanPointInPolygon($geometry2, $geometry1),
                $geometry2 instanceof LineString => Turf::booleanWithin($geometry1, $geometry2),
                $geometry2 instanceof Polygon => Turf::booleanWithin($geometry1, $geometry2),
                $geometry2 instanceof MultiPoint => self::isMultiPointInPoly($geometry1, $geometry2),
                default => false,
            };
        }

        if ($geometry1 instanceof MultiPolygon) {
            return $geometry2 instanceof Polygon ? self::isPolygonInMultiPolygon($geometry1, $geometry2) : false;
        }

        throw new InvalidArgumentException(get_class($geometry1).' geometry not supported');
    }

    private static function isPointInMultiPoint(MultiPoint $multiPoint, Point $point): bool
    {
        foreach ($multiPoint->getCoordinates() as $coord) {
            if (Helpers::compareCoords($coord, $point->getCoordinates())) {
                return true;
            }
        }

        return false;
    }

    private static function isMultiPointInPoly(Polygon $polygon, MultiPoint $multiPoint): bool
    {
        foreach ($multiPoint->getCoordinates() as $coord) {
            if (! Turf::booleanPointInPolygon(new Point($coord), $polygon)) {
                return false;
            }
        }

        return true;
    }

    private static function isPolygonInMultiPolygon(MultiPolygon $multiPolygon, Polygon $polygon): bool
    {
        foreach ($multiPolygon->getCoordinates() as $coords) {
            if (Turf::booleanWithin(new Polygon([$coords]), $polygon)) {
                return true;
            }
        }

        return false;
    }
}
