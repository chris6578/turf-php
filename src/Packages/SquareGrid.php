<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class SquareGrid
{
    public function __invoke(array $bbox, float $cellSize, string|Unit $units = Unit::KILOMETRES, ?Polygon $mask = null, array $properties = []): FeatureCollection
    {
        if (!$units instanceof Unit) {
            $units = Unit::from($units);
        }
        return Turf::rectangleGrid($bbox, $cellSize, $cellSize, $units->value, $mask, $properties);
    }
}
