<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Enums\Unit;
use Turf\Turf;

class AlongTest extends TestCase
{
    public function test_along_basic(): void
    {
        $line = new LineString([[0, 0], [0, 1]]);
        $distanceKm = 55.66; // Halfway along ~111.32 km
        $alongPoint = Turf::along($line, $distanceKm, Unit::KILOMETERS);
        $expected = Turf::destination([0, 0], $distanceKm, 0, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(
            $expected->getCoordinates(),
            $alongPoint->getGeometry()->getCoordinates(),
            0.01,
            'Point at 55.66 km should match destination calculation'
        );
    }

    public function test_along_zero_distance(): void
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $alongPoint = Turf::along($line, 0, Unit::KILOMETERS);
        $this->assertEquals(
            [0, 0],
            $alongPoint->getGeometry()->getCoordinates(),
            'Zero distance should return the start point'
        );
    }

    public function test_along_beyond_length(): void
    {
        $line = new LineString([[0, 0], [0, 1]]);
        $totalDistance = Turf::distance([0, 0], [0, 1], Unit::KILOMETERS);
        $alongPoint = Turf::along($line, $totalDistance + 10, Unit::KILOMETERS);
        $this->assertEquals(
            [0, 1],
            $alongPoint->getGeometry()->getCoordinates(),
            'Distance beyond line length should return the end point'
        );
    }

    public function test_along_with_miles(): void
    {
        $line = new LineString([[0, 0], [0, 1]]);
        $distanceKm = 55.66;
        $distanceMiles = $distanceKm / 1.60934;
        $alongPoint = Turf::along($line, $distanceMiles, Unit::MILES);
        $expected = Turf::destination([0, 0], $distanceKm, 0, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(
            $expected->getCoordinates(),
            $alongPoint->getGeometry()->getCoordinates(),
            0.01,
            'Distance in miles should match kilometers result'
        );
    }

    public function test_along_with_feature(): void
    {
        $line = new LineString([[0, 0], [0, 1]]);
        $feature = new Feature($line);
        $distanceKm = 55.66;
        $alongPoint = Turf::along($feature, $distanceKm, Unit::KILOMETERS);
        $expected = Turf::destination([0, 0], $distanceKm, 0, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(
            $expected->getCoordinates(),
            $alongPoint->getGeometry()->getCoordinates(),
            0.01,
            'Feature input should match Geometry result'
        );
    }

    public function test_along_invalid_input(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $point = new Point([0, 0]);
        Turf::along($point, 10, Unit::KILOMETERS);
    }

    public function test_along_single_point_line(): void
    {
        $line = new LineString([[0, 0], [0, 0]]); // Degenerate line
        $alongPoint = Turf::along($line, 10, Unit::KILOMETERS);
        $this->assertEquals(
            [0, 0],
            $alongPoint->getGeometry()->getCoordinates(),
            'Degenerate line should return the only point'
        );
    }
}
