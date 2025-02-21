<?php

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class BboxPolygon
{
    public function __invoke(
        array $bbox, array $properties = [], $id = null
    ): Feature {
        if (count($bbox) !== 4) {
            throw new InvalidArgumentException('BBox must have exactly 4 values: [minX, minY, maxX, maxY]');
        }

        [$west, $south, $east, $north] = array_map('floatval', $bbox);

        // Define the rectangle's corners
        $lowLeft = [$west, $south];
        $topLeft = [$west, $north];
        $topRight = [$east, $north];
        $lowRight = [$east, $south];

        // Ensure a closed Polygon (last point = first point)
        $polygonCoords = [[$lowLeft, $lowRight, $topRight, $topLeft, $lowLeft]];

        return new Feature(new Polygon($polygonCoords), $properties, $id);
    }
}
