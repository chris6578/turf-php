<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;

class BooleanPointOnLine
{
    public function __invoke(Point $point, LineString $line, bool $ignoreEndVertices = false, ?float $epsilon = 1e-10): bool
    {
        $ptCoords = $point->getCoordinates();
        $lineCoords = $line->getCoordinates();

        for ($i = 0, $count = count($lineCoords) - 1; $i < $count; $i++) {
            if (Helpers::isPointOnLineSegment(
                start: $lineCoords[$i],
                end: $lineCoords[$i + 1],
                point: $ptCoords,
                epsilon: $epsilon,
                ignoreEndVertices: $ignoreEndVertices
            )) {
                return true;
            }
        }

        return false;
    }
}
