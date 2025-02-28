<?php

namespace Turf\Tests;

use GeoJson\Geometry\Point;
use PHPUnit\Framework\TestCase;
use Turf\Enums\Unit;
use Turf\Turf;

class CircleTest extends TestCase
{
    public function test_circle_default(): void
    {
        $center = new Point([0, 0]);
        $radius = 1.0; // 1 km
        $circle = Turf::circle($center, $radius);
        $coords = $circle->getGeometry()->getCoordinates()[0];
        $this->assertCount(65, $coords, 'Circle should have 64 steps + closing point');

        $firstPoint = new Point($coords[0]);
        $distance = Turf::distance($center, $firstPoint, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(1.0, $distance, 0.01, 'Points should be 1 km from center');
    }

    public function test_circle_with_miles(): void
    {
        $center = new Point([0, 0]);
        $radiusMiles = 0.621371; // ~1 km in miles
        $circle = Turf::circle($center, $radiusMiles, 64, Unit::MILES);
        $coords = $circle->getGeometry()->getCoordinates()[0];
        $firstPoint = new Point($coords[0]);
        $distanceKm = Turf::distance($center, $firstPoint, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(1.0, $distanceKm, 0.01, 'Radius in miles should equate to 1 km');
    }
}
