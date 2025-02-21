<?php

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanWithin
{
    public function __invoke(Geometry $feature1, Geometry $feature2): bool
    {
        if ($feature1 instanceof Point && $feature2 instanceof LineString) {
            return Turf::booleanPointOnLine($feature1, $feature2);
        }

        if ($feature1 instanceof Point && $feature2 instanceof Polygon) {
            return Turf::booleanPointInPolygon($feature1, $feature2);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof Polygon) {
            return self::isLineInPolygon($feature1, $feature2);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof Polygon) {
            return self::isPolygonWithinPolygon($feature1, $feature2);
        }

        throw new InvalidArgumentException('Unsupported geometry types');
    }

    public function booleanPointOnLine(Point $point, LineString $line): bool
    {
        foreach ($line->getCoordinates() as $coord) {
            if (Helpers::compareCoords($coord, $point->getCoordinates())) {
                return true;
            }
        }

        return false;
    }

    public function booleanPointInPolygon(Point $point, Polygon $polygon): bool
    {
        foreach ($polygon->getCoordinates() as $ring) {
            foreach ($ring as $coord) {
                if (Helpers::compareCoords($coord, $point->getCoordinates())) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isLineInPolygon(LineString $line, Polygon $polygon): bool
    {
        foreach ($line->getCoordinates() as $coord) {
            if (! Turf::booleanPointInPolygon(new Point($coord), $polygon)) {
                return false;
            }
        }

        return true;
    }

    public function isPolygonWithinPolygon(Polygon $polygon1, Polygon $polygon2): bool
    {
        foreach ($polygon1->getCoordinates()[0] as $coord) {
            if (! Turf::booleanPointInPolygon(new Point($coord), $polygon2)) {
                return false;
            }
        }

        return true;
    }
}
