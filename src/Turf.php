<?php

declare(strict_types=1);

namespace willvincent\Turf;

use GeoJson\GeoJson;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Packages\TurfArea;
use willvincent\Turf\Packages\TurfCircle;
use willvincent\Turf\Packages\TurfClone;
use willvincent\Turf\Packages\TurfDestination;
use willvincent\Turf\Packages\TurfDistance;
use willvincent\Turf\Packages\TurfRewind;

class Turf
{
    /**
     * Calculate the area of a GeoJSON Feature, FeatureCollection, Polygon or MultiPolygon.
     *
     * @param GeoJson $geoJSON
     * @param string|null $units
     * @return float
     */
    public static function area(GeoJson $geoJSON, ?string $units = 'meters'): float
    {
        return (new TurfArea)($geoJSON, $units);
    }

    /**
     * Generate a circular polygon around the specified center point.
     *
     * @param array|Point $center
     * @param float $radius
     * @param int $steps
     * @param string|Unit $units
     * @param array $properties
     * @return GeoJson
     */
    public static function circle(
        array|Point $center,
        float $radius,
        int $steps = 64,
        string|Unit $units = Unit::KILOMETERS,
        array $properties = [],
    ): GeoJson
    {
        return (new TurfCircle)($center, $radius, $steps, $units, $properties);
    }

    /**
     * Clone a GeoJson object.
     * @param GeoJson $geoJSON
     * @return GeoJson
     */
    public static function clone(
        GeoJson $geoJSON,
    ): GeoJson
    {
        return (new TurfClone)($geoJSON);
    }

    /**
     * Calculate the location of a destination point from an origin point
     * given a distance in degrees, radians, miles, or kilometers;
     * and bearing in degrees.
     *
     * @param array|Point $origin
     * @param float $distance
     * @param float $bearing
     * @param string|Unit $units
     * @return Point
     */
    public static function destination(
        array|Point $origin,
        float $distance,
        float $bearing,
        string|Unit $units = Unit::KILOMETERS,
    ): Point
    {
        return (new TurfDestination)($origin, $distance, $bearing, $units);
    }

    /**
     * Calculate the distance between two points.
     *
     * @param array|Point $from
     * @param array|Point $to
     * @param string|Unit $units
     * @return float
     */
    public static function distance(
        array|Point $from,
        array|Point $to,
        string|Unit $units = Unit::KILOMETERS,
    ): float
    {
        return (new TurfDistance)($from, $to, $units);
    }

    /**
     * Rewinds a polygon or multipolygon.
     * @param Polygon|MultiPolygon $polygon
     * @param bool $reverse
     * @return Polygon|MultiPolygon
     */
    public static function rewind(
        Polygon | Multipolygon $polygon,
        bool $reverse = false,
    ): Polygon | Multipolygon
    {
        return (new TurfRewind)($polygon, $reverse);
    }
}
