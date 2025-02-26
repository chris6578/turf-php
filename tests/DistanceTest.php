<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\Point;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class DistanceTest extends TestCase
{
    public function test_distance_meridian(): void
    {
        $from = new Point([0, 0]);
        $to = new Point([0, 1]);
        $distance = Turf::distance($from, $to, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(111.32, $distance, 0.2, 'Distance along meridian should be ~111.32 km');
    }

    public function test_distance_same_point(): void
    {
        $point = new Point([0, 0]);
        $distance = Turf::distance($point, $point, Unit::KILOMETERS);
        $this->assertEquals(0, $distance, 'Distance between same points should be 0');
    }

    public function test_distance_with_miles(): void
    {
        $from = new Point([0, 0]);
        $to = new Point([0, 1]);
        $distance = Turf::distance($from, $to, Unit::MILES);
        $this->assertEqualsWithDelta(69.17, $distance, 0.1, 'Distance in miles should be ~69.17');
    }
}
