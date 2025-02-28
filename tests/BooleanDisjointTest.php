<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanDisjointTest extends TestCase
{
    public function test_disjoint_points(): void
    {
        $point1 = new Point([0, 0]);
        $point2 = new Point([1, 1]);
        $this->assertTrue(Turf::booleanDisjoint($point1, $point2), 'Different points should be disjoint');
    }

    public function test_overlapping_points(): void
    {
        $point1 = new Point([0, 0]);
        $point2 = new Point([0, 0]);
        $this->assertFalse(Turf::booleanDisjoint($point1, $point2), 'Same points should not be disjoint');
    }

    public function test_disjoint_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 0]]);
        $line2 = new LineString([[0, 1], [1, 1]]);
        $this->assertTrue(Turf::booleanDisjoint($line1, $line2), 'Non-intersecting lines should be disjoint');
    }

    public function test_intersecting_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 1]]);
        $line2 = new LineString([[0, 1], [1, 0]]);
        $this->assertFalse(Turf::booleanDisjoint($line1, $line2), 'Intersecting lines should not be disjoint');
    }

    public function test_disjoint_polygons(): void
    {
        $poly1 = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $poly2 = new Polygon([[[2, 2], [3, 2], [3, 3], [2, 3], [2, 2]]]);
        $this->assertTrue(Turf::booleanDisjoint($poly1, $poly2), 'Non-overlapping polygons should be disjoint');
    }

    public function test_overlapping_polygons(): void
    {
        $poly1 = new Polygon([[[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]]]);
        $poly2 = new Polygon([[[1, 1], [3, 1], [3, 3], [1, 3], [1, 1]]]);
        $this->assertFalse(Turf::booleanDisjoint($poly1, $poly2), 'Overlapping polygons should not be disjoint');
    }
}
