<?php

namespace willvincent\Turf\Tests;

use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class RectangleGridTest extends TestCase
{
    public function test_basic_grid(): void
    {
        $bbox = [0, 0, 1, 1];
        $grid = Turf::rectangleGrid($bbox, 0.5, 0.5, Unit::DEGREES);
        $this->assertInstanceOf(FeatureCollection::class, $grid);
        $this->assertCount(4, $grid->getFeatures(), 'Grid should have 4 cells (2x2)');

        $firstCell = $grid->getFeatures()[0]->getGeometry()->getCoordinates()[0];
        $this->assertEqualsWithDelta(
            [[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]],
            $firstCell,
            0.001,
            'First cell coordinates should match expected bounds'
        );
    }

    public function test_grid_with_kilometers(): void
    {
        $size = 50;
        $bbox = [0, 0, 0.8993, 0.8993]; // ~100 km x 100 km at equator
        $grid = Turf::rectangleGrid($bbox, $size, $size, Unit::KILOMETERS);

        $firstCell = $grid->getFeatures()[0]->getGeometry();
        $width = Turf::distance($firstCell->getCoordinates()[0][0], $firstCell->getCoordinates()[0][1], Unit::KILOMETERS);

        $this->assertCount(4, $grid->getFeatures(), 'Grid should have 4 cells (2x2)');
        $this->assertEqualsWithDelta(
            $size,
            $width,
            0.1,
            'Cell width should be approximately 50 km'
        );
    }

    public function test_grid_with_mask(): void
    {
        $bbox = [0, 0, 1, 1];
        $mask = new Polygon([[[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]]]);
        $grid = Turf::rectangleGrid($bbox, 0.5, 0.5, Unit::DEGREES, $mask);
        $this->assertCount(1, $grid->getFeatures(), 'Grid should have 1 cell within mask');
    }

    public function test_empty_grid(): void
    {
        $bbox = [0, 0, 0.1, 0.1];
        $grid = Turf::rectangleGrid($bbox, 1, 1, Unit::DEGREES);
        $this->assertCount(0, $grid->getFeatures(), 'Grid larger than bbox should be empty');
    }
}
