<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanWithinTest extends TestCase
{
    public function test_point_within_polygon(): void
    {
        $point = new Point([0.5, 0.5]);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanWithin($point, $poly),
            'Point inside polygon should return true'
        );
    }

    public function test_point_outside_polygon(): void
    {
        $point = new Point([2, 2]);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertFalse(
            Turf::booleanWithin($point, $poly),
            'Point outside polygon should return false'
        );
    }

    public function test_point_on_line(): void
    {
        $point = new Point([0.5, 0.5]);
        $line = new LineString([[0, 0], [1, 1]]);
        $this->assertTrue(
            Turf::booleanWithin($point, $line),
            'Point on line should return true'
        );
    }

    public function test_line_within_polygon(): void
    {
        $line = new LineString([[0.2, 0.2], [0.8, 0.8]]);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanWithin($line, $poly),
            'Line entirely within polygon should return true'
        );
    }

    public function test_line_crossing_polygon_boundary(): void
    {
        $line = new LineString([[0.2, 0.2], [1.2, 1.2]]);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertFalse(
            Turf::booleanWithin($line, $poly),
            'Line crossing polygon boundary should return false'
        );
    }

    public function test_polygon_within_polygon(): void
    {
        $innerPoly = new Polygon([[[0.2, 0.2], [0.4, 0.2], [0.4, 0.4], [0.2, 0.4], [0.2, 0.2]]]);
        $outerPoly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanWithin($innerPoly, $outerPoly),
            'Smaller polygon inside larger one should return true'
        );
    }

    public function test_overlapping_polygons(): void
    {
        $poly1 = new Polygon([[[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]]]);
        $poly2 = new Polygon([[[1, 1], [3, 1], [3, 3], [1, 3], [1, 1]]]);
        $this->assertFalse(
            Turf::booleanWithin($poly1, $poly2),
            'Overlapping polygons not fully within each other should return false'
        );
    }

    public function test_unsupported_geometry_types(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $point1 = new Point([0, 0]);
        $point2 = new Point([1, 1]);
        Turf::booleanWithin($point1, $point2);
    }
}
