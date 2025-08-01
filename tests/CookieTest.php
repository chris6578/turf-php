<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class CookieTest extends TestCase
{
    public function test_cookie_feature_collection_with_polygon(): void
    {
        // Create a 2x2 grid of square polygons
        $grid = new FeatureCollection([
            new Feature(new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]), ['id' => 1]),
            new Feature(new Polygon([[[1, 0], [2, 0], [2, 1], [1, 1], [1, 0]]]), ['id' => 2]),
            new Feature(new Polygon([[[0, 1], [1, 1], [1, 2], [0, 2], [0, 1]]]), ['id' => 3]),
            new Feature(new Polygon([[[1, 1], [2, 1], [2, 2], [1, 2], [1, 1]]]), ['id' => 4]),
        ]);

        // Cookie cutter that covers the right half (x >= 0.5)
        $cutter = new Polygon([[[0.5, -0.5], [2.5, -0.5], [2.5, 2.5], [0.5, 2.5], [0.5, -0.5]]]);

        $result = Turf::cookie($grid, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        // Should have some clipped features
        $this->assertGreaterThan(0, count($features));

        // All returned features should be polygons
        foreach ($features as $feature) {
            $this->assertInstanceOf(Polygon::class, $feature->getGeometry());
        }
    }

    public function test_cookie_single_polygon_with_polygon(): void
    {
        // Source polygon
        $source = new Polygon([[[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]]]);

        // Cookie cutter that covers only the top-right quarter
        $cutter = new Polygon([[[1, 1], [3, 1], [3, 3], [1, 3], [1, 1]]]);

        $result = Turf::cookie($source, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        $this->assertEquals(1, count($features));
        $this->assertInstanceOf(Polygon::class, $features[0]->getGeometry());
    }

    public function test_cookie_with_multipolygon_mask(): void
    {
        // Source polygon
        $source = new Polygon([[[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]]]);

        // MultiPolygon mask with two separate squares
        $cutter = new MultiPolygon([
            [[[1, 1], [2, 1], [2, 2], [1, 2], [1, 1]]], // First polygon
            [[[3, 3], [4, 3], [4, 4], [3, 4], [3, 3]]],  // Second polygon
        ]);

        $result = Turf::cookie($source, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        // Should return intersections with both parts of the multipolygon
        $this->assertGreaterThan(0, count($features));
    }

    public function test_cookie_with_polygon_with_hole(): void
    {
        // Source polygon
        $source = new Polygon([[[0, 0], [3, 0], [3, 3], [0, 3], [0, 0]]]);

        // Mask polygon with a hole in the middle
        $cutter = new Polygon([
            [[0, 0], [3, 0], [3, 3], [0, 3], [0, 0]], // Outer ring
            [[1, 1], [2, 1], [2, 2], [1, 2], [1, 1]],  // Hole
        ]);

        $result = Turf::cookie($source, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        $this->assertGreaterThan(0, count($features));

        // The result should respect the hole in the mask
        foreach ($features as $feature) {
            $this->assertInstanceOf(Polygon::class, $feature->getGeometry());
        }
    }

    public function test_cookie_no_intersection(): void
    {
        // Source polygon
        $source = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);

        // Cookie cutter that doesn't intersect
        $cutter = new Polygon([[[2, 2], [3, 2], [3, 3], [2, 3], [2, 2]]]);

        $result = Turf::cookie($source, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        // Should return empty FeatureCollection when no intersection
        $this->assertEquals(0, count($features));
    }

    public function test_cookie_preserves_properties(): void
    {
        // Source feature with properties
        $source = new Feature(
            new Polygon([[[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]]]),
            ['name' => 'test-polygon', 'value' => 42],
            'test-id'
        );

        // Cookie cutter that partially intersects
        $cutter = new Polygon([[[1, 1], [3, 1], [3, 3], [1, 3], [1, 1]]]);

        $result = Turf::cookie($source, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        $this->assertEquals(1, count($features));

        $maskedFeature = $features[0];
        $this->assertEquals(['name' => 'test-polygon', 'value' => 42], $maskedFeature->getProperties());
        $this->assertEquals('test-id', $maskedFeature->getId());
    }

    public function test_cookie_multipolygon_source(): void
    {
        // MultiPolygon source with two separate squares
        $source = new MultiPolygon([
            [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]], // First polygon
            [[[2, 2], [3, 2], [3, 3], [2, 3], [2, 2]]],  // Second polygon
        ]);

        // Cookie cutter that intersects both polygons
        $cutter = new Polygon([[[-0.5, -0.5], [3.5, -0.5], [3.5, 3.5], [-0.5, 3.5], [-0.5, -0.5]]]);

        $result = Turf::cookie($source, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        $this->assertGreaterThan(0, count($features));
    }

    public function test_cookie_with_square_grid_output(): void
    {
        // Create a square grid (like from squareGrid function)
        $bbox = [0, 0, 2, 2];
        $cellSize = 0.5;
        $grid = Turf::squareGrid($bbox, $cellSize);

        // Circular-ish mask
        $cutter = new Polygon([[[0.5, 0.5], [1.5, 0.5], [1.5, 1.5], [0.5, 1.5], [0.5, 0.5]]]);

        $result = Turf::cookie($grid, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        // Should have fewer features than the original grid
        $this->assertLessThan(count($grid->getFeatures()), count($features));
        $this->assertGreaterThan(0, count($features));

        // All features should be polygons
        foreach ($features as $feature) {
            $this->assertInstanceOf(Polygon::class, $feature->getGeometry());
        }
    }

    public function test_cookie_complex_jagged_shape(): void
    {
        // Create a fine grid
        $gridFeatures = [];
        for ($x = 0; $x < 20; $x++) {
            for ($y = 0; $y < 20; $y++) {
                $x1 = $x * 0.5;
                $y1 = $y * 0.5;
                $x2 = $x1 + 0.5;
                $y2 = $y1 + 0.5;

                $gridFeatures[] = new Feature(
                    new Polygon([
                        [[$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2], [$x1, $y1]],
                    ]),
                    ['x' => $x, 'y' => $y]
                );
            }
        }
        $grid = new FeatureCollection($gridFeatures);

        // Very jagged zigzag shape that will create many partial intersections
        $cutterCoords = [];
        $centerX = 5;
        $centerY = 5;
        $radius = 3;

        // Create a zigzag star with many sharp points
        for ($i = 0; $i < 32; $i++) {
            $angle = ($i / 32) * 2 * M_PI;
            // Alternate between inner and outer points
            $r = $i % 2 === 0 ? $radius : $radius * 0.3;
            $x = $centerX + $r * cos($angle);
            $y = $centerY + $r * sin($angle);
            $cutterCoords[] = [$x, $y];
        }
        // Close the ring
        $cutterCoords[] = $cutterCoords[0];

        $cutter = new Polygon([$cutterCoords]);

        $result = Turf::cookie($grid, $cutter);

        $this->assertInstanceOf(FeatureCollection::class, $result);
        $features = $result->getFeatures();

        // Should have a significant number of clipped features
        $this->assertGreaterThan(10, count($features));
        $this->assertLessThan(400, count($features)); // Less than total grid

        // All features should be polygons
        foreach ($features as $feature) {
            $geometry = $feature->getGeometry();
            $this->assertTrue(
                $geometry instanceof Polygon || $geometry instanceof MultiPolygon,
                'Feature should be Polygon or MultiPolygon'
            );
        }

        // Verify we have a mix of fully contained and partially clipped features
        $totalVertices = 0;
        $originalSquareCount = 0;

        foreach ($features as $feature) {
            $coords = $feature->getGeometry()->getCoordinates();
            $ringVertices = count($coords[0]) - 1; // Exclude closing vertex
            $totalVertices += $ringVertices;

            // Original square grid cells have 4 vertices
            if ($ringVertices === 4) {
                $originalSquareCount++;
            }
        }

        // Verify we have a good mix of contained and partially intersected features
        $this->assertGreaterThan(5, $originalSquareCount, 'Should have some fully contained squares (optimization working)');

        // Should have processed a reasonable portion of the grid
        $this->assertGreaterThan(50, count($features), 'Should process a reasonable number of grid cells');

        // Verify all features are valid polygon types
        foreach ($features as $feature) {
            $geometry = $feature->getGeometry();
            $this->assertTrue(
                $geometry instanceof Polygon || $geometry instanceof MultiPolygon,
                'All features should be Polygon or MultiPolygon'
            );
        }

        echo "\nComplex jagged test: Total features=".count($features).", Fully contained squares=$originalSquareCount\n";
    }

    public function test_cookie_contained_only_mode(): void
    {
        // Create a grid with mixed scenarios
        $source = new FeatureCollection([
            // Fully contained
            new Feature(new Polygon([[[1.5, 1.5], [2.5, 1.5], [2.5, 2.5], [1.5, 2.5], [1.5, 1.5]]]), ['id' => 'contained']),
            // Partially intersecting (should be excluded in containedOnly mode)
            new Feature(new Polygon([[[2.5, 2.5], [3.5, 2.5], [3.5, 3.5], [2.5, 3.5], [2.5, 2.5]]]), ['id' => 'partial']),
            // No intersection (should be excluded in both modes)
            new Feature(new Polygon([[[5, 5], [6, 5], [6, 6], [5, 6], [5, 5]]]), ['id' => 'none']),
        ]);

        $cutter = new Polygon([[[0, 0], [3, 0], [3, 3], [0, 3], [0, 0]]]);

        // Test normal mode (should include contained + partial)
        $normalResult = Turf::cookie($source, $cutter, false);
        $normalIds = array_map(fn ($f) => $f->getProperties()['id'], $normalResult->getFeatures());
        sort($normalIds);

        // Test contained-only mode (should include only contained)
        $containedResult = Turf::cookie($source, $cutter, true);
        $containedIds = array_map(fn ($f) => $f->getProperties()['id'], $containedResult->getFeatures());

        // Normal mode should have both contained and partial
        $this->assertContains('contained', $normalIds);
        $this->assertContains('partial', $normalIds);
        $this->assertNotContains('none', $normalIds);

        // Contained-only mode should have only contained
        $this->assertContains('contained', $containedIds);
        $this->assertNotContains('partial', $containedIds);
        $this->assertNotContains('none', $containedIds);

        $this->assertEquals(2, count($normalResult->getFeatures()), 'Normal mode should have 2 features');
        $this->assertEquals(1, count($containedResult->getFeatures()), 'Contained-only mode should have 1 feature');
    }

    public function test_cookie_partial_intersections_with_complex_shapes(): void
    {
        // Create a grid that will definitely have partial intersections
        $gridFeatures = [];
        for ($x = 0; $x < 6; $x++) {
            for ($y = 0; $y < 6; $y++) {
                $x1 = $x * 1.0;
                $y1 = $y * 1.0;
                $x2 = $x1 + 1.0;
                $y2 = $y1 + 1.0;

                $gridFeatures[] = new Feature(
                    new Polygon([
                        [[$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2], [$x1, $y1]],
                    ]),
                    ['x' => $x, 'y' => $y]
                );
            }
        }
        $grid = new FeatureCollection($gridFeatures);

        // Diamond shape that will create many partial intersections
        $cutter = new Polygon([
            [[3, 1], [5, 3], [3, 5], [1, 3], [3, 1]],
        ]);

        $result = Turf::cookie($grid, $cutter);
        $features = $result->getFeatures();

        // Count vertex types
        $squareCount = 0;
        $complexCount = 0;

        foreach ($features as $feature) {
            $coords = $feature->getGeometry()->getCoordinates();
            $vertices = count($coords[0]) - 1;

            if ($vertices === 4) {
                $squareCount++;
            } else {
                $complexCount++;
            }
        }

        // Should have both squares (fully contained) AND complex shapes (partial intersections)
        $this->assertGreaterThan(0, $squareCount, 'Should have some fully contained squares');
        $this->assertGreaterThan(0, $complexCount, 'Should have some partially intersected polygons');
        $this->assertGreaterThan(5, count($features), 'Should have a reasonable number of intersections');

        echo "\nPartial intersection test: Total=".count($features).", Squares=$squareCount, Complex=$complexCount\n";
    }
}
