<?php

namespace willvincent\Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Turf;

class AreaTest extends TestCase
{
    public function test_area(): void
    {
        // Define a small square polygon (0.0001° sides ≈ 11.132 m per side)
        $delta = 0.0001;
        $deltaRad = deg2rad($delta);
        $R = 6371008.8; // Earth radius in meters
        $expectedAreaM2 = ($deltaRad * $R) ** 2; // ≈ 123.6 m²

        $polygon = new Polygon([[
            [0, 0],
            [$delta, 0],
            [$delta, $delta],
            [0, $delta],
            [0, 0],
        ]]);

        // Test area in square meters
        $areaMeters = Turf::area($polygon, 'meters');
        $this->assertEqualsWithDelta(
            $expectedAreaM2,
            $areaMeters,
            0.1,
            'Area in square meters should match calculated value'
        );

        // Test area in square kilometers
        $areaKm = Turf::area($polygon, 'kilometers');
        $this->assertEqualsWithDelta(
            $areaMeters / 1e6,
            $areaKm,
            1e-6,
            'Area in square kilometers should match meters / 1e6'
        );

        // Test with Feature input
        $featurePolygon = new Feature($polygon);
        $areaFeature = Turf::area($featurePolygon, 'meters');
        $this->assertEqualsWithDelta(
            $expectedAreaM2,
            $areaFeature,
            0.1,
            'Feature input should produce the same area as Geometry'
        );

        // Test degenerate polygon (should return 0)
        $degeneratePolygon = new Polygon([[[0, 0], [0, 0], [0, 0], [0, 0]]]);
        $areaDegenerate = Turf::area($degeneratePolygon, 'meters');
        $this->assertEquals(
            0.0,
            $areaDegenerate,
            'Degenerate polygon should have zero area'
        );
    }
}
