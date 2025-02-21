<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Polygon;

class BooleanConcave
{
    public function __invoke(Polygon $polygon): bool
    {
        $coords = $polygon->getCoordinates();
        if (count($coords[0]) <= 4) {
            return false; // A triangle or square is always convex
        }

        $sign = null;
        $n = count($coords[0]) - 1;

        for ($i = 0; $i < $n; $i++) {
            $dx1 = $coords[0][($i + 2) % $n][0] - $coords[0][($i + 1) % $n][0];
            $dy1 = $coords[0][($i + 2) % $n][1] - $coords[0][($i + 1) % $n][1];
            $dx2 = $coords[0][$i][0] - $coords[0][($i + 1) % $n][0];
            $dy2 = $coords[0][$i][1] - $coords[0][($i + 1) % $n][1];

            $zcrossproduct = $dx1 * $dy2 - $dy1 * $dx2;

            if ($i === 0) {
                $sign = $zcrossproduct > 0;
            } elseif ($sign !== ($zcrossproduct > 0)) {
                return true; // Concave polygon detected
            }
        }

        return false; // Convex polygon
    }
}
