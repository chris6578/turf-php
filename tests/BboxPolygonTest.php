<?php

namespace Turf\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BboxPolygonTest extends TestCase
{
    public function test_bbox_polygon(): void
    {
        $bbox = [0, 0, 1, 1];
        $polygon = Turf::bboxPolygon($bbox);
        $expectedCoords = [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]];
        $this->assertEquals($expectedCoords, $polygon->getGeometry()->getCoordinates(), 'Polygon should match BBox coordinates');
    }

    public function test_bbox_polygon_with_properties(): void
    {
        $bbox = [0, 0, 1, 1];
        $properties = ['name' => 'test'];
        $id = 'testId';
        $polygon = Turf::bboxPolygon($bbox, $properties, $id);
        $this->assertEquals($properties, $polygon->getProperties(), 'Properties should be set correctly');
        $this->assertEquals($id, $polygon->getId(), 'ID should be set correctly');
    }

    public function test_invalid_bbox(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Turf::bboxPolygon([0, 0, 1]); // Too few values
    }
}
