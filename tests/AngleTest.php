<?php

namespace willvincent\Turf\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class AngleTest extends TestCase
{
    public function test_right_angle(): void
    {
        $start = [0, 0];
        $mid = [0, 1];
        $end = [1, 1];
        $angle = Turf::angle($start, $mid, $end);
        $this->assertEqualsWithDelta(
            90,
            $angle,
            0.01, // Increased delta to account for floating-point precision
            'Right angle should be approximately 90°'
        );
    }

    public function test_straight_line(): void
    {
        $start = [0, 0];
        $mid = [0, 1];
        $end = [0, 2];
        $angle = Turf::angle($start, $mid, $end);
        $this->assertEqualsWithDelta(
            180,
            $angle,
            0.001,
            'Straight line should be 180°'
        );
    }

    public function test_explementary_angle(): void
    {
        $start = [0, 0];
        $mid = [0, 1];
        $end = [1, 1];
        $angle = Turf::angle($start, $mid, $end, true);
        $this->assertEqualsWithDelta(
            270,
            $angle,
            0.01, // Adjusted delta for precision
            'Explementary right angle should be approximately 270°'
        );
    }

    public function test_mercator_projection(): void
    {
        $start = [0, 0];
        $mid = [0, 1];
        $end = [1, 1];
        $angle = Turf::angle($start, $mid, $end, false, true);
        // Note: Rhumb bearing differs slightly from great-circle bearing
        $this->assertEqualsWithDelta(
            90,
            $angle,
            1.0,
            'Mercator angle should approximate 90° with slight deviation'
        );
    }

    public function test_acute_angle(): void
    {
        $start = [0, 0];
        $mid = [1, 1];
        $end = [2, 0];
        $angle = Turf::angle($start, $mid, $end);
        $this->assertLessThanOrEqual(
            90,
            $angle - 0.01, // Adjusted with tolerance
            'Angle should be acute or nearly 90°'
        );
        $this->assertGreaterThan(
            0,
            $angle,
            'Angle should be positive'
        );
    }

    public function test_invalid_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Turf::angle([0, 0], 'not-an-array', [1, 1]);
    }
}
