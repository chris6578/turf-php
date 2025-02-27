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

class booleanValid
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
            if (count($ring) < 4 || ! self::checkRingsClose($ring) || self::checkRingsForSpikesPunctures($ring)) {
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
                if (count($ring) < 4 || ! self::checkRingsClose($ring) || self::checkRingsForSpikesPunctures($ring)) {
                    return false;
                }
                if ($j > 0 && self::polygonsIntersect($polygon[0], $ring)) {
                    return false;
                }
                if ($j === 0 && ! self::checkPolygonAgainstOthers($polygon, $polygons, $i)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param mixed[] $ring
     * @return bool
     */
    private static function checkRingsClose(array $ring): bool
    {
        return $ring[0] === end($ring);
    }

    /**
     * @param mixed[] $ring
     * @return bool
     */
    private static function checkRingsForSpikesPunctures(array $ring): bool
    {
        foreach ($ring as $i => $point) {
            for ($j = $i + 1; $j < count($ring) - 2; $j++) {
                if (Helpers::isPointOnLineSegment($ring[$j], $ring[$j + 1], $point)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed[] $poly1
     * @param mixed[] $poly2
     * @return bool
     */
    private static function polygonsIntersect(array $poly1, array $poly2): bool
    {
        foreach ($poly1 as $point) {
            if (in_array($point, $poly2, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[] $polygon
     * @param mixed[] $polygons
     * @param int $index
     * @return bool
     */
    private static function checkPolygonAgainstOthers(array $polygon, array $polygons, int $index): bool
    {
        foreach (array_slice($polygons, $index + 1) as $otherPolygon) {
            if (Turf::booleanIntersect($polygon[0], $otherPolygon[0])) {
                return false;
            }
        }

        return true;
    }
}
