<?php

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use InvalidArgumentException;
use willvincent\Turf\Enums\Unit;

class Along
{
    public function __invoke(
        GeoJson $line,
        float $distance,
        string|Unit $units = Unit::KILOMETERS
    ): Feature {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }

        if ($line instanceof Feature) {
            $line = $line->getGeometry();
        }

        if (! $line instanceof LineString) {
            throw new InvalidArgumentException('Input must be a LineString or a Feature containing a LineString.');
        }

        $coords = $line->getCoordinates();
        $traveled = 0;

        for ($i = 0; $i < count($coords) - 1; $i++) {
            $segmentStart = $coords[$i];
            $segmentEnd = $coords[$i + 1];
            $segmentDistance = Helpers::haversineDistance($segmentStart, $segmentEnd, $units);

            if ($traveled + $segmentDistance >= $distance) {
                $remaining = $distance - $traveled;
                $fraction = $remaining / $segmentDistance;
                $interpolated = self::interpolatePoint($segmentStart, $segmentEnd, $fraction);

                return new Feature(new Point($interpolated));
            }

            $traveled += $segmentDistance;
        }

        // If the distance exceeds the line length, return the last point
        return new Feature(new Point($coords[count($coords) - 1]));
    }

    /**
     * Interpolates a point between two points based on a fraction.
     *
     * @param  array  $start  The start point [lon, lat].
     * @param  array  $end  The end point [lon, lat].
     * @param  float  $fraction  The fraction along the segment (0 to 1).
     * @return array Interpolated point [lon, lat].
     */
    private static function interpolatePoint(array $start, array $end, float $fraction): array
    {
        return [
            $start[0] + $fraction * ($end[0] - $start[0]),
            $start[1] + $fraction * ($end[1] - $start[1]),
        ];
    }
}
