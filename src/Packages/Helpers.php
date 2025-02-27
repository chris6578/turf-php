<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class Helpers
{
    public const EARTH_RADIUS = 6371008.8;

    /**
     * @var array<string,int|float>
     */
    public static array $metersUnitConversion = [
        'meters' => 1,
        'metres' => 1,
        'kilometers' => 1000,
        'kilometres' => 1000,
        'miles' => 1609.34,
        'nauticalmiles' => 1852,
        'feet' => 0.3048,
        'yards' => 0.9144,
        'inches' => 0.0254,
        'centimeters' => 0.01,
        'centimetres' => 0.01,
        'millimeters' => 0.001,
        'millimetres' => 0.001,
    ];

    public static function factors(?string $factor = null): mixed
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

    public static function areaFactors(?string $factor = null): mixed
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

    public static function convertToMeters(float $distance, string|Unit $unit): float
    {
        if (! $unit instanceof Unit) {
            $unit = Unit::from($unit);
        }
        $unit = $unit->value;

        return $distance * self::$metersUnitConversion[$unit];
    }

    public static function metersToDegreesLatitude(float $meters): float
    {
        return ($meters / (M_PI * self::EARTH_RADIUS)) * 180;
    }

    public static function metersToDegreesLongitude(float $meters, float $latitude): float
    {
        $cosLat = cos(deg2rad($latitude));

        return ($meters / (M_PI * self::EARTH_RADIUS * $cosLat)) * 180;
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

    /**
     * @param float[] $start
     * @param float[] $end
     * @param float[] $point
     * @param float|null $epsilon
     * @param bool|null $ignoreEndVertices
     * @return bool
     */
    public static function isPointOnLineSegment(array $start, array $end, array $point, $epsilon = 1e-10, $ignoreEndVertices = false): bool
    {
        if ($ignoreEndVertices && ($start === $point || $end === $point)) {
            return false;
        }

        $x = $point[0];
        $y = $point[1];
        $x1 = $start[0];
        $y1 = $start[1];
        $x2 = $end[0];
        $y2 = $end[1];

        // Calculate cross product for collinearity
        $crossProduct = ($y - $y1) * ($x2 - $x1) - ($x - $x1) * ($y2 - $y1);
        if (abs($crossProduct) > $epsilon) {
            return false;
        }

        // Check if point is within segment bounds using dot product
        $dotProduct = ($x - $x1) * ($x2 - $x1) + ($y - $y1) * ($y2 - $y1);
        if ($dotProduct < -$epsilon) { // Slightly negative allowed within epsilon
            return false;
        }

        $squaredLength = ($x2 - $x1) ** 2 + ($y2 - $y1) ** 2;
        if ($dotProduct > $squaredLength + $epsilon) { // Slightly beyond allowed within epsilon
            return false;
        }

        return true;
    }

    /**
     * @param float[] $pair1
     * @param float[] $pair2
     * @return bool
     */
    public static function compareCoords(array $pair1, array $pair2): bool
    {
        return $pair1[0] === $pair2[0] && $pair1[1] === $pair2[1];
    }

    /**
     * @param Feature[] $gridFeatures
     * @param Feature|FeatureCollection|Polygon|MultiPolygon $mask
     * @return mixed[]
     */
    public static function filterGridByMask(
        array $gridFeatures,
        Feature|FeatureCollection|Polygon|MultiPolygon $mask
    ): array {
        $maskGeometry = $mask instanceof FeatureCollection
            ? array_map(fn ($feature) => $feature->getGeometry(), $mask->getFeatures())
            : ($mask instanceof Feature ? $mask->getGeometry() : $mask);

        $filteredFeatures = [];
        foreach ($gridFeatures as $feature) {
            $cellGeometry = $feature->getGeometry();
            if (is_array($maskGeometry)) {
                foreach ($maskGeometry as $geometry) {
                    if (self::anyPointInPolygon($cellGeometry->getCoordinates()[0], $geometry)) {
                        $filteredFeatures[] = $feature;
                        break;
                    }
                }
            } else {
                if (self::anyPointInPolygon($cellGeometry->getCoordinates()[0], $maskGeometry)) {
                    $filteredFeatures[] = $feature;
                }
            }
        }

        return $filteredFeatures;
    }

    /**
     * @param mixed[] $points
     * @param Polygon|MultiPolygon|Geometry|null $polygon
     * @return bool
     */
    private static function anyPointInPolygon(array $points, Polygon|MultiPolygon|Geometry|null $polygon): bool
    {
        foreach ($points as $point) {
            if (Turf::booleanPointInPolygon(new Point($point), $polygon)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param float[] $point1
     * @param float[] $point2
     * @param Unit $units
     * @return float
     */
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

    /**
     * Determines whether two line segments intersect.
     *
     * @param array $p1 Start point of first segment [x, y].
     * @param array $p2 End point of first segment [x, y].
     * @param array $p3 Start point of second segment [x, y].
     * @param array $p4 End point of second segment [x, y].
     * @return bool True if segments intersect, false otherwise.
     */
    public static function doSegmentsIntersect(array $p1, array $p2, array $p3, array $p4): bool
    {
        $orientation = function ($p, $q, $r) {
            $val = ($q[1] - $p[1]) * ($r[0] - $q[0]) - ($q[0] - $p[0]) * ($r[1] - $q[1]);
            if (abs($val) < 1e-10) return 0; // Collinear
            return ($val > 0) ? 1 : 2; // Clockwise or counterclockwise
        };

        $o1 = $orientation($p1, $p2, $p3);
        $o2 = $orientation($p1, $p2, $p4);
        $o3 = $orientation($p3, $p4, $p1);
        $o4 = $orientation($p3, $p4, $p2);

        return ($o1 !== $o2 && $o3 !== $o4); // General case: segments cross
    }
}
