<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Enums\Unit;
use willvincent\Turf\Turf;

class RectangleGrid
{
    public function __invoke(
        array $bbox,
        float $cellWidth,
        float $cellHeight,
        string|Unit $units = Unit::KILOMETERS,
        ?Polygon $mask = null,
        array $properties = []
    ): FeatureCollection {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }
        $cellWidthDeg = Helpers::convertLengthToDegrees($cellWidth, $units->value);
        $cellHeightDeg = Helpers::convertLengthToDegrees($cellHeight, $units->value);

        $minX = $bbox[0];
        $minY = $bbox[1];
        $maxX = $bbox[2];
        $maxY = $bbox[3];

        $rectangles = [];
        for ($x = $minX; $x < $maxX; $x += $cellWidthDeg) {
            for ($y = $minY; $y < $maxY; $y += $cellHeightDeg) {
                $rectangle = new Polygon([
                    [
                        [$x, $y], [$x + $cellWidthDeg, $y], [$x + $cellWidthDeg, $y + $cellHeightDeg],
                        [$x, $y + $cellHeightDeg], [$x, $y],
                    ],
                ]);

                $feature = new Feature($rectangle, $properties);

                // If a mask is provided, ensure the cell is within the mask
                if ($mask === null || Turf::booleanIntersect($rectangle, $mask)) {
                    $rectangles[] = $feature;
                }
            }
        }

        return new FeatureCollection($rectangles);
    }
}
