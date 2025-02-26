<?php

namespace willvincent\Turf\Tests;

use PHPUnit\Framework\TestCase;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class SquareGridTest extends TestCase
{
    public function test_square_grid_basic(): void
    {
        $bbox = [0, 0, 1, 1];
        $grid = Turf::squareGrid($bbox, 0.5, Unit::DEGREES);
        $this->assertCount(4, $grid->getFeatures(), 'Square grid should have 4 cells (2x2)');

        $firstCell = $grid->getFeatures()[0]->getGeometry()->getCoordinates()[0];
        $this->assertEqualsWithDelta(
            [[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]],
            $firstCell,
            0.001,
            'First cell should match expected square coordinates'
        );
    }

    public function test_square_grid_with_kilometers(): void
    {
        $bbox = [0, 0, 0.44965, 0.44965]; // ~50 km x 50 km at equator
        $grid = Turf::squareGrid($bbox, 25, Unit::KILOMETERS);
        $this->assertCount(4, $grid->getFeatures(), 'Square grid should have 4 cells (2x2)');

        $firstCell = $grid->getFeatures()[0]->getGeometry();
        $width = Turf::distance($firstCell->getCoordinates()[0][0], $firstCell->getCoordinates()[0][1], Unit::KILOMETERS);
        $this->assertEqualsWithDelta(
            25,
            $width,
            0.1,
            'Cell size should be approximately 25 km'
        );
    }
}
