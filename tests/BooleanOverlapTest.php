<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanOverlapTest extends TestCase
{
    public function test_overlapping_polygons(): void
    {
        $poly1 = new Polygon([[[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]]]);
        $poly2 = new Polygon([[[1, 1], [3, 1], [3, 3], [1, 3], [1, 1]]]);
        $this->assertTrue(
            Turf::booleanOverlap($poly1, $poly2),
            'Overlapping polygons should return true'
        );
    }

    public function test_non_overlapping_polygons(): void
    {
        $poly1 = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $poly2 = new Polygon([[[2, 2], [3, 2], [3, 3], [2, 3], [2, 2]]]);
        $this->assertFalse(
            Turf::booleanOverlap($poly1, $poly2),
            'Non-overlapping polygons should return false'
        );
    }

    public function test_intersecting_lines(): void
    {
        $line1 = new LineString([[0, 0], [2, 2]]);
        $line2 = new LineString([[0, 2], [2, 0]]);
        $this->assertTrue(
            Turf::booleanOverlap($line1, $line2),
            'Intersecting lines should return true'
        );
    }

    public function test_non_intersecting_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 0]]);
        $line2 = new LineString([[0, 1], [1, 1]]);
        $this->assertFalse(
            Turf::booleanOverlap($line1, $line2),
            'Non-intersecting lines should return false'
        );
    }

    public function test_overlapping_multi_points(): void
    {
        $multiPoint1 = new MultiPoint([[0, 0], [1, 1], [2, 2]]);
        $multiPoint2 = new MultiPoint([[1, 1], [3, 3]]);
        $this->assertTrue(
            Turf::booleanOverlap($multiPoint1, $multiPoint2),
            'MultiPoints with common point should return true'
        );
    }

    public function test_non_overlapping_multi_points(): void
    {
        $multiPoint1 = new MultiPoint([[0, 0], [1, 1]]);
        $multiPoint2 = new MultiPoint([[2, 2], [3, 3]]);
        $this->assertFalse(
            Turf::booleanOverlap($multiPoint1, $multiPoint2),
            'MultiPoints with no common points should return false'
        );
    }

    public function test_different_geometry_types(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $line = new LineString([[0, 0], [1, 1]]);
        Turf::booleanOverlap($poly, $line);
    }
}
