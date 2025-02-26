<?php

namespace willvincent\Turf\Tests;

use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class BearingTest extends TestCase
{
    public function test_bearing(): void
    {
        // North: [0,0] to [0,1]
        $bearingNorth = Turf::bearing([0, 0], [0, 1]);
        $this->assertEqualsWithDelta(
            0,
            $bearingNorth,
            0.001,
            'Bearing north should be 0°'
        );

        // Final bearing north (along meridian)
        $finalBearingNorth = Turf::bearing([0, 0], [0, 1], true);
        $this->assertEqualsWithDelta(
            0,
            $finalBearingNorth,
            0.001,
            'Final bearing north should be 0°'
        );

        // East: [0,0] to [1,0]
        $bearingEast = Turf::bearing([0, 0], [1, 0]);
        $this->assertEqualsWithDelta(
            90,
            $bearingEast,
            0.001,
            'Bearing east should be 90°'
        );

        // Same point: [0,0] to [0,0]
        $bearingSame = Turf::bearing([0, 0], [0, 0]);
        $this->assertEquals(
            0,
            $bearingSame,
            'Bearing between identical points should be 0°'
        );

        // South: [0,0] to [0,-1]
        $bearingSouth = Turf::bearing([0, 0], [0, -1]);
        $this->assertEqualsWithDelta(
            180,
            $bearingSouth,
            0.001,
            'Bearing south should be 180°'
        );

        // West: [0,0] to [-1,0]
        $bearingWest = Turf::bearing([0, 0], [-1, 0]);
        $this->assertEqualsWithDelta(
            270,
            $bearingWest,
            0.001,
            'Bearing west should be 270°'
        );
    }
}
