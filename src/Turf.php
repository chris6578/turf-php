<?php

declare(strict_types=1);

namespace Turf;

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
use Turf\Enums\Unit;
use Turf\Packages\Along;
use Turf\Packages\Angle;
use Turf\Packages\Area;
use Turf\Packages\Bbox;
use Turf\Packages\BboxClip;
use Turf\Packages\BboxPolygon;
use Turf\Packages\Bearing;
use Turf\Packages\BooleanClockwise;
use Turf\Packages\BooleanConcave;
use Turf\Packages\BooleanContains;
use Turf\Packages\BooleanCrosses;
use Turf\Packages\BooleanDisjoint;
use Turf\Packages\BooleanEqual;
use Turf\Packages\BooleanIntersect;
use Turf\Packages\BooleanOverlap;
use Turf\Packages\BooleanParallel;
use Turf\Packages\BooleanPointInPolygon;
use Turf\Packages\BooleanPointOnLine;
use Turf\Packages\BooleanTouches;
use Turf\Packages\BooleanValid;
use Turf\Packages\BooleanWithin;
use Turf\Packages\Circle;
use Turf\Packages\Cookie;
use Turf\Packages\Destination;
use Turf\Packages\Difference;
use Turf\Packages\Distance;
use Turf\Packages\Envelope;
use Turf\Packages\Kinks;
use Turf\Packages\RectangleGrid;
use Turf\Packages\Rewind;
use Turf\Packages\Simplify;
use Turf\Packages\SquareGrid;
use Turf\Packages\TurfClone;
use Turf\Packages\Union;
use Turf\Packages\UnkinkPolygon;

class Turf
{
    /**
     * Takes a LineString and returns a Point at a specified distance along the line.
     */
    public static function along(GeoJson $geoJSON, float $distance, string|Unit $units = Unit::KILOMETERS): Feature
    {
        return (new Along)($geoJSON, $distance, $units);
    }

    /**
     * @param  mixed  $startPoint
     * @param  mixed  $midPoint
     * @param  mixed  $endPoint
     */
    public static function angle(
        $startPoint,
        $midPoint,
        $endPoint,
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
     *
     * @return float[]
     */
    public static function bbox(GeoJson $geoJSON, ?bool $recompute = false): array
    {
        return (new Bbox)($geoJSON, $recompute);
    }

    /**
     * Clips a GeoJSON Feature to a given bounding box.
     *
     * @param  float[]  $bbox
     */
    public static function bboxClip(GeoJson $geoJSON, array $bbox): Feature
    {
        return (new BboxClip)($geoJSON, $bbox);
    }

    /**
     * Generates a Polygon feature from a bbox.
     *
     * @param  float[]  $bbox
     * @param  float[]  $properties
     */
    public static function bboxPolygon(array $bbox, array $properties = [], mixed $id = null): Feature
    {
        return (new BboxPolygon)($bbox, $properties, $id);
    }

    /**
     * Calculates the geographic bearing between two points.
     *
     * @param  float[]  $start
     * @param  float[]  $end
     */
    public static function bearing(array $start, array $end, bool $final = false): float
    {
        return (new Bearing)($start, $end, $final);
    }

    /**
     * Determines if a ring (LineString or Polygon) is clockwise.
     *
     * @param  LineString|Polygon|mixed[]  $geometry
     */
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
    public static function booleanPointInPolygon(Point $point, Polygon|MultiPolygon|Geometry|null $polygon, bool $ignoreBoundary = false): bool
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

    /**
     * Generate a circular polygon around the specified center point.
     *
     * @param  float[]|Point  $center
     * @param  mixed[]  $properties
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
     *  Calculate the location of a destination point from an origin point
     *  given a distance in degrees, radians, miles, or kilometers;
     *  and bearing in degrees.
     *
     * @param  float[]|Point  $origin
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
     * Finds the difference between multiple geometries by clipping the subsequent polygons from the first.
     *
     * @param  mixed[]  $properties
     */
    public static function difference(
        Feature|FeatureCollection|Polygon|MultiPolygon $geo1,
        Feature|FeatureCollection|Polygon|MultiPolygon $geo2,
        array $properties = [],
    ): Feature {
        return (new Difference)($geo1, $geo2, $properties);
    }

    /**
     * @param  float[]|Point  $from
     * @param  float[]|Point  $to
     */
    public static function distance(
        array|Point $from,
        array|Point $to,
        string|Unit $units = Unit::KILOMETERS,
    ): float {
        return (new Distance)($from, $to, $units);
    }

    /**
     * Takes any number of features and returns a Polygon feature encompassing all vertices.
     */
    public static function envelope(
        GeoJson $geoJSON,
    ): Feature {
        return (new Envelope)($geoJSON);
    }

    /**
     * Detects self-intersection in Polygons and LineStrings, and returns
     * a FeatureCollection of intersecting points.
     *
     * @param  Polygon|LineString  $geoJSON
     */
    public static function kinks(
        GeoJson $geoJSON
    ): FeatureCollection {
        return (new Kinks)($geoJSON);
    }

    /**
     * Clips input geometries using a cookie cutter polygon or multipolygon, returning only the intersecting parts.
     * Works like a cookie cutter, removing any geometry outside the cutter's outer rings or inside holes.
     *
     * @param  bool  $containedOnly  If true, only returns features that are fully contained within the cutter
     */
    public static function cookie(
        Feature|FeatureCollection|Polygon|MultiPolygon $source,
        Polygon|MultiPolygon $cutter,
        bool $containedOnly = false
    ): FeatureCollection {
        return (new Cookie)($source, $cutter, $containedOnly);
    }

    /**
     * Rewinds a polygon or multipolygon.
     */
    public static function rewind(
        GeoJson $geoJSON,
        bool $reverse = false,
    ): GeoJson|GeometryCollection|FeatureCollection|LineString|MultiLineString|Polygon|MultiPolygon {
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

    /**
     * @param  float[]  $bbox
     * @param  mixed[]  $properties
     */
    public static function rectangleGrid(
        array $bbox,
        float $cellWidth,
        float $cellHeight,
        string|Unit $units = Unit::KILOMETERS,
        Feature|FeatureCollection|Polygon|MultiPolygon|null $mask = null,
        array $properties = []
    ): FeatureCollection {
        return (new RectangleGrid)($bbox, $cellWidth, $cellHeight, $units, $mask, $properties);
    }

    /**
     * @param  float[]  $bbox
     * @param  mixed[]  $properties
     */
    public static function squareGrid(
        array $bbox,
        float $cellSize,
        string|Unit $units = Unit::KILOMETERS,
        Feature|FeatureCollection|Polygon|MultiPolygon|null $mask = null,
        array $properties = []
    ): FeatureCollection {
        return (new SquareGrid)($bbox, $cellSize, $units, $mask, $properties);
    }

    /**
     * Takes input Feature, FeatureCollection, Polygon, or MultiPolygon inputs and returns
     * a feature with one combined polygon. If the input polygons are not contiguous,
     * it returns a MultiPolygon feature.
     *
     * @param  mixed[]  $properties
     */
    public static function union(
        Feature|FeatureCollection|Polygon|MultiPolygon $geo1,
        Feature|FeatureCollection|Polygon|MultiPolygon $geo2,
        array $properties = [],
    ): Feature {
        return (new Union)($geo1, $geo2, $properties);
    }

    /** Takes a kinked polygon and returns a feature collection of polygons that have no kinks. */
    public static function unkink(
        GeoJson $geoJSON,
    ): FeatureCollection {
        return (new UnkinkPolygon)($geoJSON);
    }
}
