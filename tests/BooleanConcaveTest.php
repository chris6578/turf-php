<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class BooleanConcaveTest extends TestCase
{
    public function test_boolean_concave_convex_square(): void
    {
        $square = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertFalse(
            Turf::booleanConcave($square),
            'Square (convex) should return false'
        );
    }

    public function test_boolean_concave_concave_shape(): void
    {
        $concave = new Polygon([[[0, 0], [2, 0], [1, 1], [2, 2], [0, 2], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanConcave($concave),
            'Concave polygon should return true'
        );
    }

    public function test_boolean_concave_triangle(): void
    {
        $triangle = new Polygon([[[0, 0], [1, 0], [0.5, 1], [0, 0]]]);
        $this->assertFalse(
            Turf::booleanConcave($triangle),
            'Triangle (convex) should return false'
        );
    }
}
