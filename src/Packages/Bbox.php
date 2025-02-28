<?php

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;

class Bbox
{
    /**
     * @param GeoJson $geojson
     * @param bool $recompute
     * @return mixed[]
     */
    public function __invoke(
        GeoJson $geojson,
        bool $recompute = false
    ): array {
        // If the GeoJSON object has an existing bbox and recompute is false, return it
        if (! empty($geojson->getBoundingBox()) && ! $recompute) {
            return $geojson->getBoundingBox()->getBounds();
        }

        $minX = INF;
        $minY = INF;
        $maxX = -INF;
        $maxY = -INF;

        /**
         * Recursively extract coordinates and update the bounding box.
         *
         * @param  mixed  $geometry  A GeoJSON geometry object.
         */
        $updateBBox = function ($geometry) use (&$minX, &$minY, &$maxX, &$maxY, &$updateBBox) {
            if ($geometry instanceof Feature) {
                $updateBBox($geometry->getGeometry());
            } elseif ($geometry instanceof FeatureCollection) {
                foreach ($geometry->getFeatures() as $feature) {
                    $updateBBox($feature);
                }
            } elseif ($geometry instanceof GeometryCollection) {
                foreach ($geometry->getGeometries() as $geom) {
                    $updateBBox($geom);
                }
            } elseif ($geometry instanceof Point) {
                [$x, $y] = $geometry->getCoordinates();
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            } elseif ($geometry instanceof MultiPoint ||
                $geometry instanceof LineString ||
                $geometry instanceof MultiLineString ||
                $geometry instanceof Polygon ||
                $geometry instanceof MultiPolygon) {
                foreach ($geometry->getCoordinates() as $coord) {
                    if (is_array($coord[0])) {
                        foreach ($coord as $point) {
                            $updateBBox(new Point($point));
                        }
                    } else {
                        $updateBBox(new Point($coord));
                    }
                }
            }
        };

        // Start processing the input GeoJSON
        $updateBBox($geojson);

        return [$minX, $minY, $maxX, $maxY];
    }
}
