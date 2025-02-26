<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class BooleanCrossesTest extends TestCase
{
    public function test_multi_point_crosses_line_string(): void
    {
        $multiPoint = new MultiPoint([[0.5, 0.5], [1.5, 1.5], [2, 0]]);
        $line = new LineString([[0, 0], [2, 2]]);

        $this->assertTrue(
            Turf::booleanCrosses($multiPoint, $line),
            'MultiPoint should cross LineString if some points are on and some off'
        );
    }

    public function test_line_string_crosses_polygon(): void
    {
        $line = new LineString([[0, 0], [2, 2]]);
        $polygon = new Polygon([[[1, 0], [3, 0], [3, 2], [1, 2], [1, 0]]]);

        $this->assertTrue(
            Turf::booleanCrosses($line, $polygon),
            'LineString should cross Polygon'
        );
    }

    public function test_line_strings_cross(): void
    {
        $line1 = new LineString([[0, 0], [2, 2]]);
        $line2 = new LineString([[0, 2], [2, 0]]);

        $this->assertTrue(
            Turf::booleanCrosses($line1, $line2),
            'Crossing LineStrings should return true'
        );
    }
}
