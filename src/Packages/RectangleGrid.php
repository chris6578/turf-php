<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Enums\Unit;

class RectangleGrid
{
    public function __invoke(
        array $bbox,
        float $cellWidth,
        float $cellHeight,
        string|Unit $units = Unit::KILOMETERS,
        Feature|FeatureCollection|Polygon|MultiPolygon|null $mask = null,
        array $properties = []
    ): FeatureCollection {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }
        [$deltaPhi, $deltaLambda] = self::calculateGridCellSizes($bbox, $cellWidth, $cellHeight, $units);

        $lonMin = $bbox[0];
        $latMin = $bbox[1];
        $lonMax = $bbox[2];
        $latMax = $bbox[3];

        $features = [];
        $lat = $latMin;
        while ($lat < $latMax) {
            $lon = $lonMin;
            while ($lon < $lonMax) {
                $polygon = new Polygon([[
                    [$lon, $lat],
                    [$lon + $deltaLambda, $lat],
                    [$lon + $deltaLambda, $lat + $deltaPhi],
                    [$lon, $lat + $deltaPhi],
                    [$lon, $lat],
                ]]);

                $features[] = new Feature($polygon);
                $lon += $deltaLambda;
            }
            $lat += $deltaPhi;
        }

        if ($mask !== null) {
            $features = Helpers::filterGridByMask($features, $mask);
        }

        return new FeatureCollection($features);
    }

    private static function calculateGridCellSizes(
        array $bbox,
        float $cellWidth,
        float $cellHeight,
        string|Unit $units = Unit::KILOMETERS
    ): array {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }

        $latMin = $bbox[1];
        $latMax = $bbox[3];
        $phiAvg = ($latMin + $latMax) / 2;
        $phiAvgRad = deg2rad($phiAvg);

        if ($units === 'degrees') {
            // If units are degrees, use values directly
            $deltaPhi = $cellHeight;
            $deltaLambda = $cellWidth;
        } else {
            // Convert cell height to radians
            $radiansHeight = Helpers::lengthToRadians($cellHeight, $units);
            // Convert to degrees for latitude
            $deltaPhi = rad2deg($radiansHeight);

            // Convert cell width to radians
            $radiansWidth = Helpers::lengthToRadians($cellWidth, $units);
            // Adjust for longitude at average latitude
            $deltaLambda = rad2deg($radiansWidth / cos($phiAvgRad));
        }

        return [$deltaPhi, $deltaLambda];
    }
}
