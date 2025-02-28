<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BboxClipTest extends TestCase
{
    public function test_clip_line_string(): void
    {
        $line = new LineString([[-1, -1], [0.5, 0.5], [2, 2]]);
        $bbox = [0, 0, 1, 1];
        $clipped = Turf::bboxClip($line, $bbox);
        $coords = $clipped->getGeometry()->getCoordinates();
        $this->assertEquals([[0, 0], [0.5, 0.5], [1, 1]], $coords, 'LineString should be clipped to bbox with intermediate point');
    }

    public function test_clip_polygon(): void
    {
        $polygon = new Polygon([[[-0.5, -0.5], [1.5, -0.5], [1.5, 1.5], [-0.5, 1.5], [-0.5, -0.5]]]);
        $bbox = [0, 0, 1, 1];
        $clipped = Turf::bboxClip($polygon, $bbox);
        $coords = $clipped->getGeometry()->getCoordinates()[0];
        $this->assertEquals([[0, 1], [1, 1], [1, 0], [0, 0], [0, 1]], $coords, 'Polygon should be clipped to bbox');
    }

    public function test_clip_feature(): void
    {
        $feature = new Feature(new LineString([[-1, -1], [2, 2]]));
        $bbox = [0, 0, 1, 1];
        $clipped = Turf::bboxClip($feature, $bbox);
        $coords = $clipped->getGeometry()->getCoordinates();
        $this->assertEquals([[0, 0], [1, 1]], $coords, 'Feature should be clipped correctly');
    }

    public function test_invalid_geometry(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $point = new Point([0, 0]);
        Turf::bboxClip($point, [0, 0, 1, 1]);
    }
}
