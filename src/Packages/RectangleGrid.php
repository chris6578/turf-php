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

        if ($deltaPhi > ($latMax - $latMin) || $deltaLambda > ($lonMax - $lonMin)) {
            return new FeatureCollection([]);
        }

        $numLatCells = (int) ceil(($latMax - $latMin) / $deltaPhi);
        $numLonCells = (int) ceil(($lonMax - $lonMin) / $deltaLambda);

        $features = [];
        for ($i = 0; $i < $numLatCells; $i++) {
            $lat = $latMin + $i * $deltaPhi;
            $cellLatMax = min($lat + $deltaPhi, $latMax); // Clip to bbox
            for ($j = 0; $j < $numLonCells; $j++) {
                $lon = $lonMin + $j * $deltaLambda;
                $cellLonMax = min($lon + $deltaLambda, $lonMax); // Clip to bbox
                $polygon = new Polygon([[
                    [$lon, $lat],
                    [$cellLonMax, $lat],
                    [$cellLonMax, $cellLatMax],
                    [$lon, $cellLatMax],
                    [$lon, $lat],
                ]]);
                $features[] = new Feature($polygon);
            }
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

        if ($units === Unit::DEGREES) { // Fixed unit check
            $deltaPhi = $cellHeight;
            $deltaLambda = $cellWidth;
        } else {
            $radiansHeight = Helpers::lengthToRadians($cellHeight, $units);
            $deltaPhi = rad2deg($radiansHeight);
            $radiansWidth = Helpers::lengthToRadians($cellWidth, $units);
            $deltaLambda = rad2deg($radiansWidth / cos($phiAvgRad));
        }

        return [$deltaPhi, $deltaLambda];
    }
}
