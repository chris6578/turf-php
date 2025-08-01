<?php

declare(strict_types=1);

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use Polyclip\Clipper;
use Turf\Turf;

class Cookie
{
    public function __invoke(
        Feature|FeatureCollection|Polygon|MultiPolygon $source,
        Feature|FeatureCollection|Polygon|MultiPolygon $cutter,
        bool $containedOnly = false
    ): FeatureCollection {
        // Extract geometry from cutter if it's a Feature or FeatureCollection
        $cutterGeometry = $this->extractCutterGeometry($cutter);
        if ($cutterGeometry === null) {
            return new FeatureCollection([]);
        }

        // Pre-compute cutter bounding box for spatial filtering
        $cutterBbox = Turf::bbox($cutterGeometry);

        // Collect all features to process
        $sourceFeatures = [];

        if ($source instanceof FeatureCollection) {
            $sourceFeatures = $source->getFeatures();
        } elseif ($source instanceof Feature) {
            $sourceFeatures = [$source];
        } else {
            // It's a Polygon or MultiPolygon, wrap it in a Feature
            $sourceFeatures = [new Feature($source)];
        }

        $clippedFeatures = [];

        foreach ($sourceFeatures as $feature) {
            $geometry = $feature->getGeometry();

            // Only process polygon-like geometries
            if (! ($geometry instanceof Polygon) && ! ($geometry instanceof MultiPolygon)) {
                continue;
            }

            // Early spatial filtering - skip if bounding boxes don't intersect
            $featureBbox = Turf::bbox($geometry);
            if (! $this->bboxesIntersect($featureBbox, $cutterBbox)) {
                continue;
            }

            // Check if feature is fully contained within cutter
            $isFullyContained = $this->isFullyContained($featureBbox, $cutterBbox, $geometry, $cutterGeometry);

            if ($containedOnly) {
                // Only include fully contained features
                if ($isFullyContained) {
                    // Use rewind to ensure consistent winding without expensive clipping
                    $rewindedGeometry = Turf::rewind($geometry);
                    $clippedFeatures[] = new Feature(
                        $rewindedGeometry instanceof Polygon || $rewindedGeometry instanceof MultiPolygon ? $rewindedGeometry : null,
                        $feature->getProperties(),
                        $feature->getId()
                    );
                }

                continue;
            }

            // Normal mode: include both contained and intersecting features
            if ($isFullyContained) {
                // Optimization: pass through contained features with just winding normalization
                $rewindedGeometry = Turf::rewind($geometry);
                $clippedFeatures[] = new Feature(
                    $rewindedGeometry instanceof Polygon || $rewindedGeometry instanceof MultiPolygon ? $rewindedGeometry : null,
                    $feature->getProperties(),
                    $feature->getId()
                );

                continue;
            }

            // Compute intersection for partially intersecting features
            $intersection = Clipper::intersection($geometry, $cutterGeometry);
            $intersectionGeometry = $intersection->getGeometry();
            if ($intersectionGeometry === null) {
                continue;
            }

            if ($intersectionGeometry instanceof Polygon || $intersectionGeometry instanceof MultiPolygon) {
                // Basic validation - just check that coordinates exist
                $coords = $intersectionGeometry->getCoordinates();
                if (! empty($coords)) {
                    $clippedFeatures[] = new Feature(
                        $intersectionGeometry,
                        $feature->getProperties(),
                        $feature->getId()
                    );
                }
            }
        }

        return new FeatureCollection($clippedFeatures);
    }

    /**
     * Extract geometry from cutter input
     */
    private function extractCutterGeometry(Feature|FeatureCollection|Polygon|MultiPolygon $cutter): Polygon|MultiPolygon|null
    {
        if ($cutter instanceof Polygon || $cutter instanceof MultiPolygon) {
            return $cutter;
        }

        if ($cutter instanceof Feature) {
            $geometry = $cutter->getGeometry();
            $polygons = $this->extractPolygonsFromGeometry($geometry);
            
            if (empty($polygons)) {
                return null;
            }
            
            // If only one polygon, return as Polygon, otherwise as MultiPolygon
            if (count($polygons) === 1) {
                return new Polygon($polygons[0]);
            }
            
            return new MultiPolygon($polygons);
        }

        // Must be FeatureCollection at this point
        $features = $cutter->getFeatures();
        if (empty($features)) {
            return null;
        }

        // Collect all valid geometries from the FeatureCollection
        $polygons = [];
        $skippedCount = 0;
        
        foreach ($features as $feature) {
            $geometry = $feature->getGeometry();
            $extracted = $this->extractPolygonsFromGeometry($geometry);
            
            if (!empty($extracted)) {
                $polygons = array_merge($polygons, $extracted);
            } else {
                $skippedCount++;
            }
        }
        
        // Log a warning if we had to skip non-polygon geometries
        if ($skippedCount > 0 && !empty($polygons)) {
            // Note: In a real application, you might want to use proper logging
            // For now, we silently continue as long as we have at least one valid polygon
        }

        if (empty($polygons)) {
            return null;
        }

        // If only one polygon, return as Polygon, otherwise as MultiPolygon
        if (count($polygons) === 1) {
            return new Polygon($polygons[0]);
        }

        return new MultiPolygon($polygons);
    }

    /**
     * Extract polygon coordinates from various geometry types
     * 
     * @param mixed $geometry The geometry to extract polygons from
     * @return array<array<array<array<float>>>> Array of polygon coordinate arrays
     */
    private function extractPolygonsFromGeometry($geometry): array
    {
        if ($geometry === null) {
            return [];
        }

        if ($geometry instanceof Polygon) {
            return [$geometry->getCoordinates()];
        }

        if ($geometry instanceof MultiPolygon) {
            return $geometry->getCoordinates();
        }

        if ($geometry instanceof GeometryCollection) {
            $polygons = [];
            foreach ($geometry->getGeometries() as $subGeometry) {
                $extracted = $this->extractPolygonsFromGeometry($subGeometry);
                $polygons = array_merge($polygons, $extracted);
            }
            return $polygons;
        }

        // For non-polygon geometries (Point, LineString, etc.), we could potentially:
        // 1. Buffer them to create polygons (requires additional library)
        // 2. Convert simple rectangles from LineStrings
        // 3. Create bounding box polygons
        // For now, we skip them gracefully but could extend this in the future
        
        return [];
    }

    /**
     * Fast bounding box intersection test
     *
     * @param  float[]  $bbox1  [minX, minY, maxX, maxY]
     * @param  float[]  $bbox2  [minX, minY, maxX, maxY]
     */
    private function bboxesIntersect(array $bbox1, array $bbox2): bool
    {
        return ! ($bbox1[2] < $bbox2[0] || // bbox1.maxX < bbox2.minX
                 $bbox1[0] > $bbox2[2] || // bbox1.minX > bbox2.maxX
                 $bbox1[3] < $bbox2[1] || // bbox1.maxY < bbox2.minY
                 $bbox1[1] > $bbox2[3]);  // bbox1.minY > bbox2.maxY
    }

    /**
     * Check if a feature is fully contained within the cutter
     * Uses fast bbox check first, then more expensive geometry check if needed
     *
     * @param  float[]  $featureBbox
     * @param  float[]  $cutterBbox
     */
    private function isFullyContained(
        array $featureBbox,
        array $cutterBbox,
        Polygon|MultiPolygon $geometry,
        Polygon|MultiPolygon $cutter
    ): bool {
        // Fast bbox containment check first
        if (! $this->bboxContains($cutterBbox, $featureBbox)) {
            return false;
        }

        // Be conservative: only optimize for simple rectangular cases
        // For single polygon cutter without holes AND the cutter is rectangular, bbox containment is sufficient
        if ($cutter instanceof Polygon && count($cutter->getCoordinates()) === 1) {
            $cutterRing = $cutter->getCoordinates()[0];
            // Check if cutter is axis-aligned rectangle (4 or 5 points - last point might be duplicate)
            $ringSize = count($cutterRing);
            if (($ringSize === 4 || $ringSize === 5) && $this->isAxisAlignedRectangle($cutterRing)) {
                return true; // Feature bbox is contained and cutter is simple rectangle
            }
        }

        // For complex cases (MultiPolygon, Polygon with holes, or non-rectangular polygons),
        // skip optimization - let intersection handle it correctly
        return false;
    }

    /**
     * Check if bbox1 fully contains bbox2
     *
     * @param  float[]  $bbox1  [minX, minY, maxX, maxY] - container
     * @param  float[]  $bbox2  [minX, minY, maxX, maxY] - contained
     */
    private function bboxContains(array $bbox1, array $bbox2): bool
    {
        return $bbox1[0] <= $bbox2[0] && // container.minX <= contained.minX
               $bbox1[1] <= $bbox2[1] && // container.minY <= contained.minY
               $bbox1[2] >= $bbox2[2] && // container.maxX >= contained.maxX
               $bbox1[3] >= $bbox2[3];   // container.maxY >= contained.maxY
    }

    /**
     * Check if a ring represents an axis-aligned rectangle
     *
     * @param  float[][]  $ring
     */
    private function isAxisAlignedRectangle(array $ring): bool
    {
        // Remove duplicate closing point if present
        $points = count($ring) === 5 && $ring[0] === $ring[4] ? array_slice($ring, 0, 4) : $ring;

        if (count($points) !== 4) {
            return false;
        }

        // Check if all points form axis-aligned rectangle
        // Extract unique X and Y coordinates
        $xCoords = array_unique(array_column($points, 0));
        $yCoords = array_unique(array_column($points, 1));

        // Should have exactly 2 unique X and 2 unique Y coordinates
        return count($xCoords) === 2 && count($yCoords) === 2;
    }
}
