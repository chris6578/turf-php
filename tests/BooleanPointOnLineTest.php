<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class BooleanPointOnLineTest extends TestCase
{
    public function test_point_on_segment(): void
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $point = new Point([0.5, 0.5]);
        $this->assertTrue(
            Turf::booleanPointOnLine($point, $line),
            'Point on line segment should return true'
        );
    }

    public function test_point_off_line(): void
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $point = new Point([0.5, 0.6]);
        $this->assertFalse(
            Turf::booleanPointOnLine($point, $line),
            'Point off line should return false'
        );
    }

    public function test_point_at_endpoint(): void
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $startPoint = new Point([0, 0]);
        $this->assertTrue(
            Turf::booleanPointOnLine($startPoint, $line),
            'Point at endpoint should return true by default'
        );
        $this->assertFalse(
            Turf::booleanPointOnLine($startPoint, $line, true),
            'Point at endpoint with ignoreEndVertices should return false'
        );
    }

    public function test_point_near_line_with_epsilon(): void
    {
        $line = new LineString([[0, 0], [1, 0]]);
        $point = new Point([0.5, 0.0001]); // Slightly off
        $this->assertFalse(
            Turf::booleanPointOnLine($point, $line),
            'Point slightly off line should return false with default epsilon'
        );
        $this->assertTrue(
            Turf::booleanPointOnLine($point, $line, false, 0.001),
            'Point near line with larger epsilon should return true'
        );
    }
}
