<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\Point;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class DestinationTest extends TestCase
{
    public function test_destination_north(): void
    {
        $origin = new Point([0, 0]);
        $dest = Turf::destination($origin, 100, 0, Unit::KILOMETERS);
        $this->assertEqualsWithDelta([0, 0.8993], $dest->getCoordinates(), 0.001, 'North 100 km should increase latitude');
    }

    public function test_destination_east(): void
    {
        $origin = new Point([0, 0]);
        $dest = Turf::destination($origin, 100, 90, Unit::KILOMETERS);
        $this->assertEqualsWithDelta([0.8993, 0], $dest->getCoordinates(), 0.001, 'East 100 km should increase longitude');
    }

    public function test_destination_with_miles(): void
    {
        $origin = new Point([0, 0]);
        $dest = Turf::destination($origin, 62.1371, 0, Unit::MILES); // ~100 km
        $this->assertEqualsWithDelta([0, 0.8993], $dest->getCoordinates(), 0.001, 'Miles should match kilometers');
    }
}
