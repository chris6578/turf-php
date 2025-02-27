<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class BooleanTouchesTest extends TestCase
{
    public function test_point_touches_line_end(): void
    {
        $point = new Point([0, 0]);
        $line = new LineString([[0, 0], [1, 1]]);
        $this->assertTrue(
            Turf::booleanTouches($point, $line),
            'Point at line endpoint should return true'
        );
    }

    public function test_point_inside_line(): void
    {
        $point = new Point([0.5, 0.5]);
        $line = new LineString([[0, 0], [1, 1]]);
        $this->assertFalse(
            Turf::booleanTouches($point, $line),
            'Point inside line should return false'
        );
    }

    public function test_lines_touch_at_endpoint(): void
    {
        $line1 = new LineString([[0, 0], [1, 1]]);
        $line2 = new LineString([[1, 1], [2, 2]]);
        $this->assertTrue(
            Turf::booleanTouches($line1, $line2),
            'Lines touching at endpoint should return true'
        );
    }

    public function test_lines_intersect(): void
    {
        $line1 = new LineString([[0, 0], [2, 2]]);
        $line2 = new LineString([[0, 2], [2, 0]]);
        $this->assertFalse(
            Turf::booleanTouches($line1, $line2),
            'Intersecting lines should return false'
        );
    }

    public function test_line_touches_polygon_boundary(): void
    {
        $line = new LineString([[0, 0], [1, 0]]);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanTouches($line, $poly),
            'Line on polygon boundary should return true'
        );
    }

    public function test_line_inside_polygon(): void
    {
        $line = new LineString([[0.2, 0.2], [0.8, 0.8]]);
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertFalse(
            Turf::booleanTouches($line, $poly),
            'Line inside polygon should return false'
        );
    }

    public function test_unsupported_geometry_types(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $point1 = new Point([0, 0]);
        $point2 = new Point([1, 1]);
        Turf::booleanTouches($point1, $point2);
    }
}
