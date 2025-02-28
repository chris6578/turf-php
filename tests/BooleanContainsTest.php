<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanContainsTest extends TestCase
{
    public function test_point_contains_point(): void
    {
        $point1 = new Point([0, 0]);
        $point2 = new Point([0, 0]);
        $point3 = new Point([1, 1]);
        $this->assertTrue(Turf::booleanContains($point1, $point2), 'Identical points should be contained');
        $this->assertFalse(Turf::booleanContains($point1, $point3), 'Different points should not be contained');
    }

    public function test_line_string_contains_point(): void
    {
        $line = new LineString([[0, 0], [1, 1]]);
        $pointOn = new Point([0.5, 0.5]);
        $pointOff = new Point([0.5, 0.6]);
        $this->assertTrue(Turf::booleanContains($line, $pointOn), 'Point on LineString should be contained');
        $this->assertFalse(Turf::booleanContains($line, $pointOff), 'Point off LineString should not be contained');
    }

    public function test_polygon_contains_point(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $pointIn = new Point([0.5, 0.5]);
        $pointOut = new Point([2, 2]);
        $this->assertTrue(Turf::booleanContains($polygon, $pointIn), 'Point inside polygon should be contained');
        $this->assertFalse(Turf::booleanContains($polygon, $pointOut), 'Point outside polygon should not be contained');
    }

    public function test_multi_point_contains_point(): void
    {
        $multiPoint = new MultiPoint([[0, 0], [1, 1], [2, 2]]);
        $pointIn = new Point([1, 1]);
        $pointOut = new Point([3, 3]);

        $this->assertTrue(
            Turf::booleanContains($multiPoint, $pointIn),
            'MultiPoint should contain one of its points'
        );
        $this->assertFalse(
            Turf::booleanContains($multiPoint, $pointOut),
            'MultiPoint should not contain external point'
        );
    }
}
