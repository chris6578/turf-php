<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanEqualTest extends TestCase
{
    public function test_equal_points(): void
    {
        $point1 = new Point([0, 0]);
        $point2 = new Point([0, 0]);
        $this->assertTrue(Turf::booleanEqual($point1, $point2), 'Identical points should be equal');
    }

    public function test_different_points(): void
    {
        $point1 = new Point([0, 0]);
        $point2 = new Point([1, 1]);
        $this->assertFalse(Turf::booleanEqual($point1, $point2), 'Different points should not be equal');
    }

    public function test_equal_line_strings(): void
    {
        $line1 = new LineString([[0, 0], [1, 1]]);
        $line2 = new LineString([[0, 0], [1, 1]]);
        $this->assertTrue(Turf::booleanEqual($line1, $line2), 'Identical LineStrings should be equal');
    }

    public function test_different_line_strings(): void
    {
        $line1 = new LineString([[0, 0], [1, 1]]);
        $line2 = new LineString([[0, 0], [1, 0]]);
        $this->assertFalse(Turf::booleanEqual($line1, $line2), 'Different LineStrings should not be equal');
    }
}
