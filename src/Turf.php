<?php

declare(strict_types=1);

namespace willvincent\Turf;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry;
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
use willvincent\Turf\Packages\BooleanClockwise;
use willvincent\Turf\Packages\BooleanConcave;
use willvincent\Turf\Packages\BooleanContains;
use willvincent\Turf\Packages\BooleanCrosses;
use willvincent\Turf\Packages\BooleanDisjoint;
use willvincent\Turf\Packages\BooleanEqual;
use willvincent\Turf\Packages\BooleanIntersect;
use willvincent\Turf\Packages\BooleanOverlap;
use willvincent\Turf\Packages\BooleanParallel;
use willvincent\Turf\Packages\BooleanPointInPolygon;
use willvincent\Turf\Packages\BooleanPointOnLine;
use willvincent\Turf\Packages\BooleanTouches;
use willvincent\Turf\Packages\booleanValid;
use willvincent\Turf\Packages\BooleanWithin;
use willvincent\Turf\Packages\Circle;
use willvincent\Turf\Packages\Destination;
use willvincent\Turf\Packages\Distance;
use willvincent\Turf\Packages\Kinks;
use willvincent\Turf\Packages\RectangleGrid;
use willvincent\Turf\Packages\Rewind;
use willvincent\Turf\Packages\Simplify;
use willvincent\Turf\Packages\SquareGrid;
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

    /** Determines if a ring (LineString or Polygon) is clockwise. */
    public static function booleanClockwise(LineString|Polygon|array $geometry): bool
    {
        return (new BooleanClockwise)($geometry);
    }

    /** Determines if a polygon is concave. */
    public static function booleanConcave(Polygon $polygon): bool
    {
        return (new BooleanConcave)($polygon);
    }

    /** Determines if one geometry completely contains another. */
    public static function booleanContains(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanContains)($geometry1, $geometry2);
    }

    /** Determines if two geometries cross each other. */
    public static function booleanCrosses(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanCrosses)($geometry1, $geometry2);
    }

    /** Determines if two geometries are disjoint (i.e., they do not intersect). */
    public static function booleanDisjoint(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanDisjoint)($geometry1, $geometry2);
    }

    /** Determines if two geometries are equal by comparing their coordinate values. */
    public static function booleanEqual(Geometry $geometry1, Geometry $geometry2, int $precision = 6): bool
    {
        return (new BooleanEqual)($geometry1, $geometry2, $precision);
    }

    /** Determines if two geometries intersect. */
    public static function booleanIntersect(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanIntersect)($geometry1, $geometry2);
    }

    /** Determines if two geometries overlap. */
    public static function booleanOverlap(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanOverlap)($geometry1, $geometry2);
    }

    /** Determines if two LineStrings are parallel. */
    public static function booleanParallel(LineString $line1, LineString $line2): bool
    {
        return (new BooleanParallel)($line1, $line2);
    }

    /** Determines if a point is within a polygon. */
    public static function booleanPointInPolygon(Point $point, Polygon|MultiPolygon $polygon, bool $ignoreBoundary = false): bool
    {
        return (new BooleanPointInPolygon)($point, $polygon, $ignoreBoundary);
    }

    /** Determines if a point is on a line segment. */
    public static function booleanPointOnLine(Point $point, LineString $line, bool $ignoreEndVertices = false, ?float $epsilon = null): bool
    {
        return (new BooleanPointOnLine)($point, $line, $ignoreEndVertices, $epsilon);
    }

    /** Determines if geometries touch each other. */
    public static function booleanTouches(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanTouches)($geometry1, $geometry2);
    }

    /** Determines if a geometry is valid according to the OGC Simple Feature Specification. */
    public static function booleanValid(Geometry $geometry): bool
    {
        return (new BooleanValid)($geometry);
    }

    /** Determines if the first geometry is completely within the second geometry. */
    public static function booleanWithin(Geometry $geometry1, Geometry $geometry2): bool
    {
        return (new BooleanWithin)($geometry1, $geometry2);
    }

    /** Generate a circular polygon around the specified center point. */
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

    public static function rectangleGrid(
        array $bbox,
        float $cellWidth,
        float $cellHeight,
        string|Unit $units = Unit::KILOMETERS,
        ?Polygon $mask = null,
        array $properties = []
    ): FeatureCollection {
        return (new RectangleGrid)($bbox, $cellWidth, $cellHeight, $units, $mask, $properties);
    }

    /** Creates a grid of square polygons within a bounding box. */
    public static function squareGrid(array $bbox, float $cellSize, string|Unit $units = Unit::KILOMETERS, ?Polygon $mask = null, array $properties = []): FeatureCollection
    {
        return (new SquareGrid)($bbox, $cellSize, $units, $mask, $properties);
    }

    /** Takes a kinked polygon and returns a feature collection of polygons that have no kinks. */
    public static function unkink(
        GeoJson $geoJSON,
    ): FeatureCollection {
        return (new UnkinkPolygon)($geoJSON);
    }
}
