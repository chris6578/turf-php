<?php

namespace willvincent\Turf\Tests;

use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class BooleanPointInPolygonTest extends TestCase
{
    public function test_point_inside_polygon(): void
    {
        $polygon = new Polygon([[
            [0, 0], [1, 0], [1, 1], [0, 1], [0, 0], // Square
        ]]);
        $point = new Point([0.5, 0.5]);
        $this->assertTrue(
            Turf::booleanPointInPolygon($point, $polygon),
            'Point inside polygon should return true'
        );
    }

    public function test_point_outside_polygon(): void
    {
        $polygon = new Polygon([[
            [0, 0], [1, 0], [1, 1], [0, 1], [0, 0],
        ]]);
        $point = new Point([2, 2]);
        $this->assertFalse(
            Turf::booleanPointInPolygon($point, $polygon),
            'Point outside polygon should return false'
        );
    }

    public function test_point_on_boundary(): void
    {
        $polygon = new Polygon([[
            [0, 0], [1, 0], [1, 1], [0, 1], [0, 0],
        ]]);
        $point = new Point([0.5, 0]); // On bottom edge
        $this->assertTrue(
            Turf::booleanPointInPolygon($point, $polygon),
            'Point on boundary should return true by default'
        );
        $this->assertFalse(
            Turf::booleanPointInPolygon($point, $polygon, true),
            'Point on boundary with ignoreBoundary should return false'
        );
    }

    public function test_point_in_multi_polygon(): void
    {
        $multiPolygon = new MultiPolygon([
            [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]], // First polygon
            [[[2, 2], [3, 2], [3, 3], [2, 3], [2, 2]]],  // Second polygon
        ]);
        $pointInFirst = new Point([0.5, 0.5]);
        $pointInSecond = new Point([2.5, 2.5]);
        $pointOutside = new Point([1.5, 1.5]);
        $this->assertTrue(
            Turf::booleanPointInPolygon($pointInFirst, $multiPolygon),
            'Point in first polygon should return true'
        );
        $this->assertTrue(
            Turf::booleanPointInPolygon($pointInSecond, $multiPolygon),
            'Point in second polygon should return true'
        );
        $this->assertFalse(
            Turf::booleanPointInPolygon($pointOutside, $multiPolygon),
            'Point outside both polygons should return false'
        );
    }
}
