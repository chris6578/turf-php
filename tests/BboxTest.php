<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BboxTest extends TestCase
{
    public function test_bbox_point(): void
    {
        $point = new Point([10, 20]);
        $bbox = Turf::bbox($point);
        $this->assertEquals([10, 20, 10, 20], $bbox, 'Point BBox should be [lon, lat, lon, lat]');
    }

    public function test_bbox_line_string(): void
    {
        $line = new LineString([[0, 0], [1, 1], [2, 2]]);
        $bbox = Turf::bbox($line);
        $this->assertEquals([0, 0, 2, 2], $bbox, 'LineString BBox should encompass all points');
    }

    public function test_bbox_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 1], [1, 0], [0, 0]]]);
        $bbox = Turf::bbox($polygon);
        $this->assertEquals([0, 0, 1, 1], $bbox, 'Polygon BBox should cover all ring points');
    }

    public function test_bbox_feature(): void
    {
        $feature = new Feature(new LineString([[0, 0], [1, 1]]));
        $bbox = Turf::bbox($feature);
        $this->assertEquals([0, 0, 1, 1], $bbox, 'Feature BBox should match its geometry');
    }

    public function test_existing_bbox(): void
    {
        $feature = GeoJson::jsonUnserialize([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [10, 20],
            ],
            'properties' => [],
            'bbox' => [5, 5, 15, 25],
        ]);

        $bbox = Turf::bbox($feature);
        $this->assertEquals([5, 5, 15, 25], $bbox, 'Should return existing BBox when recompute is false');
    }

    public function test_recompute_bbox(): void
    {
        $feature = GeoJson::jsonUnserialize([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [10, 20],
            ],
            'properties' => [],
            'bbox' => [5, 5, 15, 25],
        ]);
        $bbox = Turf::bbox($feature, true);
        $this->assertEquals([10, 20, 10, 20], $bbox, 'Should recompute BBox when recompute is true');
    }
}
