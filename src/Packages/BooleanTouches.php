<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class BooleanTouches
{
    public function __invoke(Geometry $geometry1, Geometry $geometry2): bool
    {
        if ($geometry1 instanceof Point && $geometry2 instanceof LineString) {
            return self::isPointOnLineEnd($geometry1, $geometry2);
        }

        if ($geometry1 instanceof LineString && $geometry2 instanceof Point) {
            return self::isPointOnLineEnd($geometry2, $geometry1);
        }

        if ($geometry1 instanceof LineString && $geometry2 instanceof LineString) {
            return self::doLineStringsTouch($geometry1, $geometry2);
        }

        if ($geometry1 instanceof Polygon && $geometry2 instanceof LineString) {
            return self::isLineTouchingPolygon($geometry2, $geometry1);
        }

        if ($geometry1 instanceof LineString && $geometry2 instanceof Polygon) {
            return self::isLineTouchingPolygon($geometry1, $geometry2);
        }

        throw new InvalidArgumentException('Unsupported geometry types');
    }

    private static function isPointOnLineEnd(Point $point, LineString $line): bool
    {
        $coords = $line->getCoordinates();

        return Helpers::compareCoords($coords[0], $point->getCoordinates()) ||
            Helpers::compareCoords($coords[count($coords) - 1], $point->getCoordinates());
    }

    private static function doLineStringsTouch(LineString $line1, LineString $line2): bool
    {
        $coords1 = $line1->getCoordinates();
        $coords2 = $line2->getCoordinates();

        return Helpers::compareCoords($coords1[0], $coords2[0]) ||
            Helpers::compareCoords($coords1[0], $coords2[count($coords2) - 1]) ||
            Helpers::compareCoords($coords1[count($coords1) - 1], $coords2[0]) ||
            Helpers::compareCoords($coords1[count($coords1) - 1], $coords2[count($coords2) - 1]);
    }

    private static function isLineTouchingPolygon(LineString $line, Polygon $polygon): bool
    {
        foreach ($polygon->getCoordinates()[0] as $coord) {
            if (self::isPointOnLineEnd(new Point($coord), $line)) {
                return true;
            }
        }

        return false;
    }
}
