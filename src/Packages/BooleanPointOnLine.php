<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;

class BooleanPointOnLine
{
    public function __invoke(Point $point, LineString $line, bool $ignoreEndVertices = false, ?float $epsilon = null): bool
    {
        $ptCoords = $point->getCoordinates();
        $lineCoords = $line->getCoordinates();

        for ($i = 0, $count = count($lineCoords) - 1; $i < $count; $i++) {
            $excludeBoundary = false;
            if ($ignoreEndVertices) {
                if ($i === 0) {
                    $excludeBoundary = 'start';
                } elseif ($i === $count - 1) {
                    $excludeBoundary = 'end';
                }
            }
            if (Helpers::isPointOnLineSegment($lineCoords[$i], $lineCoords[$i + 1], $ptCoords, $excludeBoundary, $epsilon)) {
                return true;
            }
        }

        return false;
    }
}
