<?php

declare(strict_types=1);

namespace willvincent\Turf;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Packages\Along;
use willvincent\Turf\Packages\Angle;
use willvincent\Turf\Packages\Area;
use willvincent\Turf\Packages\Bbox;
use willvincent\Turf\Packages\BboxClip;
use willvincent\Turf\Packages\BboxPolygon;
use willvincent\Turf\Packages\Bearing;
use willvincent\Turf\Packages\Circle;
use willvincent\Turf\Packages\Destination;
use willvincent\Turf\Packages\Distance;
use willvincent\Turf\Packages\Kinks;
use willvincent\Turf\Packages\Rewind;
use willvincent\Turf\Packages\Simplify;
use willvincent\Turf\Packages\TurfClone;
use willvincent\Turf\Packages\UnkinkPolygon;

class Turf
{
    /**
     * Takes a LineString and returns a Point at a specified distance along the line.
     */
    public static function along(GeoJson $geoJSON, float $distance, string|Unit $units = Unit::KILOMETERS): Feature
    {
        return (new Along)($geoJSON, $distance, $units);
    }

    public static function angle(array $startPoint,
        array $midPoint,
        array $endPoint,
        bool $explementary = false,
        bool $mercator = false): float
    {
        return (new Angle)($startPoint, $midPoint, $endPoint, $explementary, $mercator);
    }

    /**
     * Calculate the area of a GeoJSON Feature, FeatureCollection, Polygon or MultiPolygon.
     */
    public static function area(GeoJson $geoJSON, ?string $units = 'meters'): float
    {
        return (new Area)($geoJSON, $units);
    }

    /**
     * Calculate the bounding box (BBox) of a given GeoJSON object.
     */
    public static function bbox(GeoJson $geoJSON, ?bool $recompute = false): array
    {
        return (new Bbox)($geoJSON, $recompute);
    }

    /**
     * Clips a GeoJSON Feature to a given bounding box.
     */
    public static function bboxClip(GeoJson $geoJSON, array $bbox): Feature
    {
        return (new BboxClip)($geoJSON, $bbox);
    }

    /**
     * Generates a Polygon feature from a bbox.
     */
    public static function bboxPolygon(array $bbox, array $properties = [], $id = null): Feature
    {
        return (new BboxPolygon)($bbox, $properties, $id);
    }

    /** Calculates the geographic bearing between two points. */
    public static function bearing(array $start, array $end, bool $final = false): float
    {
        return (new Bearing)($start, $end, $final);
    }

    /**
     * Generate a circular polygon around the specified center point.
     */
    public static function circle(
        array|Point $center,
        float $radius,
        int $steps = 64,
        string|Unit $units = Unit::KILOMETERS,
        array $properties = [],
    ): GeoJson {
        return (new Circle)($center, $radius, $steps, $units, $properties);
    }

    /**
     * Clone a GeoJson object.
     */
    public static function clone(
        GeoJson $geoJSON,
    ): GeoJson {
        return (new TurfClone)($geoJSON);
    }

    /**
     * Calculate the location of a destination point from an origin point
     * given a distance in degrees, radians, miles, or kilometers;
     * and bearing in degrees.
     */
    public static function destination(
        array|Point $origin,
        float $distance,
        float $bearing,
        string|Unit $units = Unit::KILOMETERS,
    ): Point {
        return (new Destination)($origin, $distance, $bearing, $units);
    }

    /**
     * Calculate the distance between two points.
     */
    public static function distance(
        array|Point $from,
        array|Point $to,
        string|Unit $units = Unit::KILOMETERS,
    ): float {
        return (new Distance)($from, $to, $units);
    }

    /**
     * Detects self-intersection in Polygons and LineStrings, and returns
     * a FeatureCollection of intersecting points.
     *
     * @param  Polygon|LineString  $geometry
     */
    public static function kinks(
        GeoJson $geoJSON
    ): FeatureCollection {
        return (new Kinks)($geoJSON);
    }

    /**
     * Rewinds a polygon or multipolygon.
     *
     * @param  GeoJson  $polygon
     */
    public static function rewind(
        GeoJson $geoJSON,
        bool $reverse = false,
    ): GeometryCollection|FeatureCollection|LineString|MultiLineString|Polygon|MultiPolygon {
        return (new Rewind)($geoJSON, $reverse);
    }

    /**
     * Simplifies a GeoJSON object using the Ramer-Douglas-Peucker algorithm.
     */
    public static function simplify(
        GeoJson $geoJSON,
        ?float $tolerance = 1.0,
        ?bool $highQuality = false
    ): Feature|FeatureCollection|GeometryCollection|GeoJson {
        return (new Simplify)($geoJSON);
    }

    /** Takes a kinked polygon and returns a feature collection of polygons that have no kinks. */
    public static function unkink(
        GeoJson $geoJSON,
    ): FeatureCollection {
        return (new UnkinkPolygon)($geoJSON);
    }
}
