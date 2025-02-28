<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanValidTest extends TestCase
{
    public function test_valid_point(): void
    {
        $point = new Point([0, 0]);
        $this->assertTrue(
            Turf::booleanValid($point),
            'Simple point should be valid'
        );
    }

    public function test_valid_line(): void
    {
        $line = new LineString([[0, 0], [1, 1], [2, 2]]);
        $this->assertTrue(
            Turf::booleanValid($line),
            'Line without duplicates should be valid'
        );
    }

    public function test_invalid_line_with_insufficient_points(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('LineString requires at least two positions');
        $line = new LineString([[0, 0]]);
    }

    public function test_valid_polygon(): void
    {
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanValid($poly),
            'Simple closed polygon should be valid'
        );
    }

    public function test_self_intersecting_polygon(): void
    {
        $poly = new Polygon([[[0, 0], [1, 1], [0, 1], [1, 0], [0, 0]]]); // Figure-eight
        $this->assertFalse(
            Turf::booleanValid($poly),
            'Self-intersecting polygon should be invalid'
        );
    }

    public function test_unclosed_polygon(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('LinearRing requires the first and last positions to be equivalent');
        $poly = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1]]]);
    }
}
