<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Turf;

class BooleanValid
{
    public function __invoke(Geometry $feature): bool
    {
        switch (get_class($feature)) {
            case Point::class:
                return count($feature->getCoordinates()) > 1;
            case MultiPoint::class:
            case LineString::class:
            case MultiLineString::class:
                return self::validateMultiPointOrLineString($feature);
            case Polygon::class:
                return self::validatePolygon($feature);
            case MultiPolygon::class:
                return self::validateMultiPolygon($feature);
            default:
                return false;
        }
    }

    private static function validateMultiPointOrLineString(Geometry $feature): bool
    {
        if ($feature instanceof LineString) {
            $coords = $feature->getCoordinates();
            if (count($coords) < 2) {
                return false;
            }
            $hasDistinct = false;
            for ($i = 1; $i < count($coords); $i++) {
                if ($coords[$i][0] !== $coords[0][0] || $coords[$i][1] !== $coords[0][1]) {
                    $hasDistinct = true;
                    break;
                }
            }
            if (!$hasDistinct) {
                return false; // All points are identical
            }
        }
        foreach ($feature->getCoordinates() as $coord) {
            if (count($coord) < 2) {
                return false;
            }
        }
        return true;
    }

    private static function validatePolygon(Polygon $polygon): bool
    {
        $rings = $polygon->getCoordinates();
        foreach ($rings as $i => $ring) {
            if (count($ring) < 4 || !self::checkRingsClose($ring) || self::hasSelfIntersections($ring)) {
                return false;
            }
            if ($i > 0 && self::polygonsIntersect($rings[0], $ring)) {
                return false;
            }
        }
        return true;
    }

    private static function validateMultiPolygon(MultiPolygon $multiPolygon): bool
    {
        $polygons = $multiPolygon->getCoordinates();
        foreach ($polygons as $i => $polygon) {
            foreach ($polygon as $j => $ring) {
                if (count($ring) < 4 || !self::checkRingsClose($ring) || self::hasSelfIntersections($ring)) {
                    return false;
                }
                if ($j > 0 && self::polygonsIntersect($polygon[0], $ring)) {
                    return false;
                }
                if ($j === 0 && !self::checkPolygonAgainstOthers($polygon, $polygons, $i)) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function checkRingsClose(array $ring): bool
    {
        return $ring[0] === end($ring);
    }

    private static function hasSelfIntersections(array $ring): bool
    {
        for ($i = 0; $i < count($ring) - 1; $i++) {
            for ($j = $i + 2; $j < count($ring) - 1; $j++) {
                if ($i === 0 && $j === count($ring) - 2) {
                    continue; // Skip closing segment
                }
                if (Helpers::doSegmentsIntersect($ring[$i], $ring[$i + 1], $ring[$j], $ring[$j + 1])) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function polygonsIntersect(array $poly1, array $poly2): bool
    {
        foreach ($poly1 as $point) {
            if (in_array($point, $poly2, true)) {
                return true;
            }
        }
        return false;
    }

    private static function checkPolygonAgainstOthers(array $polygon, array $polygons, int $index): bool
    {
        foreach (array_slice($polygons, $index + 1) as $otherPolygon) {
            if (Turf::booleanIntersect(new Polygon([$polygon[0]]), new Polygon([$otherPolygon[0]]))) {
                return false;
            }
        }
        return true;
    }
}
