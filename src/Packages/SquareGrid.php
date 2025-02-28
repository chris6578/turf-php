<?php

declare(strict_types=1);

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use Turf\Enums\Unit;
use Turf\Turf;

class SquareGrid
{
    /**
     * @param float[] $bbox
     * @param float $cellSize
     * @param string|Unit $units
     * @param Feature|FeatureCollection|Polygon|MultiPolygon|null $mask
     * @param mixed[] $properties
     * @return FeatureCollection
     */
    public function __invoke(
        array $bbox,
        float $cellSize,
        string|Unit $units = Unit::KILOMETRES,
        Feature|FeatureCollection|Polygon|MultiPolygon|null $mask = null,
        array $properties = []
    ): FeatureCollection {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }

        return Turf::rectangleGrid($bbox, $cellSize, $cellSize, $units->value, $mask, $properties);
    }
}
