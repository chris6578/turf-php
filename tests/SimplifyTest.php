<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class SimplifyTest extends TestCase
{
    public function test_simplify_line_string(): void
    {
        $line = new LineString([[0, 0], [0.1, 0.1], [0.2, 0], [1, 0]]);
        $simplified = Turf::simplify($line, 0.15);
        $coords = $simplified->getCoordinates();

        $this->assertEquals(
            [[0, 0], [1, 0]],
            $coords,
            'LineString should be simplified to start and end points'
        );
    }

    public function test_simplify_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [0.1, 0.1], [0.2, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $simplified = Turf::simplify($polygon, 0.15);
        $coords = $simplified->getCoordinates()[0];

        $this->assertEquals(
            [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]],
            $coords,
            'Polygon should be simplified, removing minor deviations'
        );
    }

    public function test_simplify_invalid_tolerance(): void
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $simplified = Turf::simplify($line, -1);
        $this->assertEquals(
            $line, $simplified
        );
    }
}
