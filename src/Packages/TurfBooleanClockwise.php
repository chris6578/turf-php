<?php

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Polygon;

class TurfBooleanClockwise
{
    /**
     * @param LineString|Polygon $line
     * @return bool
     */
    public function __invoke(
        LineString | Polygon $line
    ): bool
    {
        $ring = $line->getCoordinates();
        $sum = 0;
        $i = 1;
        $prev = $cur = null;

        while ($i < count($ring)) {
            $prev = $cur ?: $ring[0];
            $cur = $ring[$i];
            $sum += ($cur[0] - $prev[0]) * ($cur[1] + $prev[1]);
            $i++;
        }

        return $sum > 0;
    }
}
