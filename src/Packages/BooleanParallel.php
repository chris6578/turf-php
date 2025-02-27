<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\LineString;

class BooleanParallel
{
    public function __invoke(LineString $line1, LineString $line2): bool
    {
        $segments1 = self::getLineSegments($line1);
        $segments2 = self::getLineSegments($line2);

        foreach ($segments1 as $index => $segment1) {
            if (! isset($segments2[$index])) {
                break;
            }
            if (! self::isParallel($segment1, $segments2[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extracts segments from a LineString.
     *
     * @param  LineString  $line  The LineString.
     * @return mixed[] Segments of the LineString.
     */
    private static function getLineSegments(LineString $line): array
    {
        $coordinates = $line->getCoordinates();
        $segments = [];
        for ($i = 0; $i < count($coordinates) - 1; $i++) {
            $segments[] = [$coordinates[$i], $coordinates[$i + 1]];
        }

        return $segments;
    }

    /**
     * Determines if two segments are parallel by comparing their slopes.
     *
     * @param  mixed[]  $segment1  First segment.
     * @param  mixed[]  $segment2  Second segment.
     * @return bool True if the segments are parallel, false otherwise.
     */
    private static function isParallel(array $segment1, array $segment2): bool
    {
        $slope1 = self::calculateSlope($segment1[0], $segment1[1]);
        $slope2 = self::calculateSlope($segment2[0], $segment2[1]);

        return $slope1 === $slope2 || abs($slope2 - $slope1) % 180 === 0;
    }

    /**
     * Calculates the slope between two points.
     *
     * @param  float[]  $point1  First point.
     * @param  float[]  $point2  Second point.
     * @return float The slope angle in degrees.
     */
    private static function calculateSlope(array $point1, array $point2): float
    {
        return rad2deg(atan2($point2[1] - $point1[1], $point2[0] - $point1[0]));
    }
}
