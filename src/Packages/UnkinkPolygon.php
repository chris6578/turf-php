<?php

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;

class UnkinkPolygon
{
    public function __invoke(GeoJson $geojson): FeatureCollection
    {
        $features = [];

        // Flatten MultiPolygon and FeatureCollection
        $polygons = self::flattenPolygons($geojson);

        foreach ($polygons as $polygonFeature) {
            $simplePolygons = self::splitSelfIntersectingPolygon($polygonFeature);

            foreach ($simplePolygons as $poly) {
                $features[] = new Feature(new Polygon($poly->getGeometry()->getCoordinates()), $polygonFeature->getProperties());
            }
        }

        return new FeatureCollection($features);
    }

    /**
     * Flattens a GeoJSON object into an array of Polygon Features.
     *
     * @param  GeoJson  $geojson  A FeatureCollection, Feature, Polygon, or MultiPolygon.
     * @return mixed[] An array of Polygon Features.
     */
    private static function flattenPolygons(GeoJson $geojson): array
    {
        $polygons = [];

        if ($geojson instanceof FeatureCollection) {
            foreach ($geojson->getFeatures() as $feature) {
                $polygons = array_merge($polygons, self::flattenPolygons($feature));
            }
        } elseif ($geojson instanceof Feature) {
            if ($geojson->getGeometry() instanceof MultiPolygon) {
                foreach ($geojson->getGeometry()->getCoordinates() as $polygonCoords) {
                    $polygons[] = new Feature(new Polygon($polygonCoords), $geojson->getProperties());
                }
            } elseif ($geojson->getGeometry() instanceof Polygon) {
                $polygons[] = $geojson;
            }
        } elseif ($geojson instanceof MultiPolygon) {
            foreach ($geojson->getCoordinates() as $polygonCoords) {
                $polygons[] = new Feature(new Polygon($polygonCoords));
            }
        } elseif ($geojson instanceof Polygon) {
            $polygons[] = new Feature($geojson);
        }

        return $polygons;
    }

    /**
     * Splits a self-intersecting polygon into valid simple polygons.
     *
     * @param  Feature  $polygonFeature  A self-intersecting polygon feature.
     * @return mixed[] An array of simple polygon features.
     */
    private static function splitSelfIntersectingPolygon(Feature $polygonFeature): array
    {
        $polygonCoords = $polygonFeature->getGeometry()->getCoordinates();

        // 1. Detect self-intersections
        $intersections = self::detectIntersections($polygonCoords[0]);

        // 2. If no self-intersections, return the original polygon
        if (empty($intersections)) {
            return [$polygonFeature];
        }

        // 3. Split the polygon at intersections into simple polygons
        return self::performPolygonSplitting($polygonCoords[0], $intersections);
    }

    /**
     * Detects self-intersections in a polygon ring.
     *
     * @param  mixed[]  $ring  The polygon's outer ring coordinates.
     * @return mixed[] List of intersection points.
     */
    private static function detectIntersections(array $ring): array
    {
        $intersections = [];

        for ($i = 0; $i < count($ring) - 1; $i++) {
            for ($j = $i + 2; $j < count($ring) - 1; $j++) {
                if ($i === 0 && $j === count($ring) - 2) {
                    continue; // Skip first/last edge to avoid false intersections
                }

                $intersect = self::lineIntersection($ring[$i], $ring[$i + 1], $ring[$j], $ring[$j + 1]);
                if ($intersect) {
                    $intersections[] = $intersect;
                }
            }
        }

        return $intersections;
    }

    /**
     * Finds the intersection point of two line segments.
     *
     * @param  float[]  $p1  Line 1 start [x, y].
     * @param  float[]  $p2  Line 1 end [x, y].
     * @param  float[]  $p3  Line 2 start [x, y].
     * @param  float[]  $p4  Line 2 end [x, y].
     * @return float[]|null Intersection point or null.
     */
    private static function lineIntersection(array $p1, array $p2, array $p3, array $p4): ?array
    {
        [$x1, $y1] = $p1;
        [$x2, $y2] = $p2;
        [$x3, $y3] = $p3;
        [$x4, $y4] = $p4;

        $den = ($x1 - $x2) * ($y3 - $y4) - ($y1 - $y2) * ($x3 - $x4);
        if ($den == 0) {
            return null;
        } // Parallel lines

        $t = (($x1 - $x3) * ($y3 - $y4) - ($y1 - $y3) * ($x3 - $x4)) / $den;
        $u = -((($x1 - $x2) * ($y1 - $y3) - ($y1 - $y2) * ($x1 - $x3)) / $den);

        if ($t > 0 && $t < 1 && $u > 0 && $u < 1) {
            return [$x1 + $t * ($x2 - $x1), $y1 + $t * ($y2 - $y1)];
        }

        return null;
    }

    /**
     * Splits a polygon into valid simple polygons at intersection points.
     *
     * @param  mixed[]  $ring  The outer ring of the polygon.
     * @param  mixed[]  $intersections  List of intersection points.
     * @return mixed[] List of simple polygons.
     */
    private static function performPolygonSplitting(array $ring, array $intersections): array
    {
        $splitPolygons = [];

        // Naive approach: Break into smaller polygons using intersections
        // TODO: Implement a full ear-clipping or constrained triangulation algorithm

        // For now, return individual triangles as a placeholder
        for ($i = 0; $i < count($ring) - 2; $i++) {
            $splitPolygons[] = new Feature(new Polygon([[
                $ring[$i],
                $ring[$i + 1],
                $intersections[0] ?? $ring[$i + 2], // Use first intersection if found
                $ring[$i],
            ]]));
        }

        return $splitPolygons;
    }
}
