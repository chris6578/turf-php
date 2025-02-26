<?php

namespace willvincent\Turf\Tests;

use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class KinksTest extends TestCase
{
    public function test_simple_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $kinks = Turf::kinks($polygon);
        $this->assertInstanceOf(FeatureCollection::class, $kinks);
        $this->assertEmpty($kinks->getFeatures(), 'Simple polygon should have no kinks');
    }

    public function test_self_intersecting_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 1], [0, 1], [1, 0], [0, 0]]]); // Figure-eight
        $kinks = Turf::kinks($polygon);
        $this->assertNotEmpty($kinks->getFeatures(), 'Self-intersecting polygon should have kinks');
        $this->assertEqualsWithDelta([0.5, 0.5], $kinks->getFeatures()[0]->getGeometry()->getCoordinates(), 0.01, 'Intersection point should be at [0.5, 0.5]');
    }
}
