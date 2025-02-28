<?php

namespace Turf\Tests;

use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanClockwiseTest extends TestCase
{
    public function test_clockwise_ring(): void
    {
        $ring = [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]];
        $this->assertTrue(Turf::booleanClockwise($ring), 'Clockwise ring should return true');
    }

    public function test_counter_clockwise_ring(): void
    {
        $ring = [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]];
        $this->assertFalse(Turf::booleanClockwise($ring), 'Counterclockwise ring should return false');
    }

    public function test_invalid_ring(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Turf::booleanClockwise([[0, 0]]); // Too few points
    }

    public function test_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(Turf::booleanClockwise($polygon), 'Clockwise polygon should return true');
    }

    public function testBooleanClockwiseWithArray(): void
    {
        $clockwiseRing = [[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]];
        $counterClockwiseRing = [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]];

        $this->assertTrue(
            Turf::booleanClockwise($clockwiseRing),
            'Clockwise ring array should return true'
        );
        $this->assertFalse(
            Turf::booleanClockwise($counterClockwiseRing),
            'Counterclockwise ring array should return false'
        );
    }

    public function testBooleanClockwiseWithPolygon(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $this->assertTrue(
            Turf::booleanClockwise($polygon),
            'Clockwise polygon should return true'
        );
    }

    public function testBooleanClockwiseInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidRing = [[0, 0]]; // Too few points
        Turf::booleanClockwise($invalidRing);
    }
}
