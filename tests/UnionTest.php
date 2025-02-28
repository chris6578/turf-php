<?php

namespace Turf\Tests;

use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class UnionTest extends TestCase
{
    public function test_overlapping_polygons()
    {
        $poly1 = new Polygon([
            [
                [-82.574787, 35.594087],
                [-82.574787, 35.615581],
                [-82.545261, 35.615581],
                [-82.545261, 35.594087],
                [-82.574787, 35.594087],
            ],
        ]);

        $poly2 = new Polygon([
            [
                [-82.560024, 35.585153],
                [-82.560024, 35.602602],
                [-82.52964, 35.602602],
                [-82.52964, 35.585153],
                [-82.560024, 35.585153],
            ],
        ]);

        $union = Turf::union($poly1, $poly2);
        $geometry = $union->getGeometry();
        $this->assertInstanceOf(Polygon::class, $geometry);
    }

    public function test_non_overlapping_polygons()
    {
        $poly1 = new Polygon([
            [
                [-82.57527, 35.61644],
                [-82.57527, 35.59650],
                [-82.54152, 35.59650],
                [-82.54152, 35.61644],
                [-82.57527, 35.61644],
            ],
        ]);

        $poly2 = new Polygon([
            [
                [-82.53823, 35.57322],
                [-82.50177, 35.57322],
                [-82.50177, 35.59190],
                [-82.53823, 35.59190],
                [-82.53823, 35.57322],
            ],
        ]);

        $union = Turf::union($poly1, $poly2);
        $geometry = $union->getGeometry();
        $this->assertInstanceOf(MultiPolygon::class, $geometry);
    }
}
