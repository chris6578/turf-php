<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class Helpers
{
    public const EARTH_RADIUS = 6371008.8;

    public static function factors(?string $factor = null): int|float|array
    {
        $factors = [
            'centimeters' => self::EARTH_RADIUS * 100,
            'centimetres' => self::EARTH_RADIUS * 100,
            'degrees' => 360 / (2 * M_PI),
            'feet' => self::EARTH_RADIUS * 3.28084,
            'inches' => self::EARTH_RADIUS * 39.37,
            'kilometers' => self::EARTH_RADIUS / 1000,
            'kilometres' => self::EARTH_RADIUS / 1000,
            'meters' => self::EARTH_RADIUS,
            'metres' => self::EARTH_RADIUS,
            'miles' => self::EARTH_RADIUS / 1609.344,
            'millimeters' => self::EARTH_RADIUS * 1000,
            'millimetres' => self::EARTH_RADIUS * 1000,
            'nauticalmiles' => self::EARTH_RADIUS / 1852,
            'radians' => 1,
            'yards' => self::EARTH_RADIUS * 1.0936,
        ];

        if ($factor) {
            return $factors[$factor];
        }

        return $factors;
    }

    public static function areaFactors(?string $factor = null): int|float|array
    {
        $factors = [
            'acres' => 0.000247105,
            'centimeters' => 10000,
            'centimetres' => 10000,
            'feet' => 10.763910417,
            'hectares' => 0.0001,
            'inches' => 1550.003100006,
            'kilometers' => 0.000001,
            'kilometres' => 0.000001,
            'meters' => 1,
            'metres' => 1,
            'miles' => 3.86e-7,
            'nauticalmiles' => 2.9155334959812285e-7,
            'millimeters' => 1000000,
            'millimetres' => 1000000,
            'yards' => 1.195990046,
        ];

        if ($factor) {
            return $factors[$factor];
        }

        return $factors;
    }

    public static function radiansToLength(
        float $radians,
        string|Unit $units = Unit::KILOMETERS,
    ): float|int {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }
        $factor = self::factors()[$units->value];

        return $radians * $factor;
    }

    public static function lengthToRadians(
        float $distance,
        string|Unit $units = Unit::KILOMETERS,
    ): float|int {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }

        // Special case for degrees
        if ($units == Unit::DEGREES) {
            return $distance * (M_PI / 180);
        }

        return $distance / self::factors()[$units->value];
    }

    public static function isPointOnLineSegment(array $start, array $end, array $point): bool
    {
        $crossProduct = ($point[1] - $start[1]) * ($end[0] - $start[0]) - ($point[0] - $start[0]) * ($end[1] - $start[1]);
        if (abs($crossProduct) > 1e-10) {
            return false;
        }

        $dotProduct = ($point[0] - $start[0]) * ($end[0] - $start[0]) + ($point[1] - $start[1]) * ($end[1] - $start[1]);
        if ($dotProduct < 0) {
            return false;
        }

        $squaredLength = ($end[0] - $start[0]) ** 2 + ($end[1] - $start[1]) ** 2;

        return $dotProduct <= $squaredLength;
    }

    public static function compareCoords(array $pair1, array $pair2): bool
    {
        return $pair1[0] === $pair2[0] && $pair1[1] === $pair2[1];
    }

    public static function filterGridByMask(
        array $gridFeatures,
        Feature|FeatureCollection|Polygon|MultiPolygon $mask
    ): array {
        switch ($mask->getType()) {
            case 'FeatureCollection':
                $maskGeometry = array_map(fn ($feature) => $feature->getGeometry(), $mask->getFeatures());
                break;
            case 'Feature':
                $maskGeometry = $mask->getGeometry();
                break;
            default:
                $maskGeometry = $mask;
                break;
        }

        $filteredFeatures = [];
        foreach ($gridFeatures as $feature) {
            $cellGeometry = $feature->getGeometry();
            if (is_array($maskGeometry)) {
                foreach ($maskGeometry as $geometry) {
                    if (Turf::booleanIntersect($cellGeometry, $geometry)) {
                        $filteredFeatures[] = $feature;
                    }
                }
            } else {
                if (Turf::booleanIntersect($cellGeometry, $maskGeometry)) {
                    $filteredFeatures[] = $feature;
                }
            }
        }
        return $filteredFeatures;
    }

    public static function haversineDistance(
        array $point1,
        array $point2,
        Unit $units = Unit::KILOMETERS): float
    {
        if (in_array($units, [Unit::MILES, Unit::KILOMETERS, Unit::RADIANS, Unit::DEGREES])) {
            $earthRadius = Helpers::factors($units->value);
        } else {
            throw new InvalidArgumentException("Invalid units. Use 'kilometers', 'miles', 'degrees', or 'radians'.");
        }

        [$lon1, $lat1] = array_map('deg2rad', $point1);
        [$lon2, $lat2] = array_map('deg2rad', $point2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
