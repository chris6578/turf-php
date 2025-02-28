<?php

declare(strict_types=1);

namespace Turf\Packages;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class BooleanClockwise
{
    /**
     * @param  LineString|Polygon|mixed[]  $geometry
     */
    public function __invoke(LineString|Polygon|array $geometry): bool
    {
        // Extract coordinates from the input
        if ($geometry instanceof Polygon) {
            $ring = $geometry->getCoordinates()[0]; // Only check the outer ring
        } elseif ($geometry instanceof LineString) {
            $ring = $geometry->getCoordinates();
        } else {
            $ring = $geometry;
        }

        if (count($ring) < 2) {
            throw new InvalidArgumentException('A LineString or Polygon must have at least two coordinates.');
        }

        $sum = 0;
        $prev = $ring[0];

        for ($i = 1; $i < count($ring); $i++) {
            $cur = $ring[$i];
            $sum += ($cur[0] - $prev[0]) * ($cur[1] + $prev[1]);
            $prev = $cur;
        }

        return $sum < 0;
    }
}
