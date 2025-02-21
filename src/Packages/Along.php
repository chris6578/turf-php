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
            $segmentDistance = self::haversineDistance($segmentStart, $segmentEnd, $units);

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
     * Computes the Haversine distance between two points.
     *
     * @param  array  $point1  The first point [lon, lat].
     * @param  array  $point2  The second point [lon, lat].
     * @param  string  $units  Distance units ('kilometers', 'miles', 'degrees', 'radians').
     * @return float Distance between the points.
     */
    public function haversineDistance(
        array $point1,
        array $point2,
        Unit $units = Unit::KILOMETERS): float
    {
        if (in_array($units, [Unit::MILES, Unit::KILOMETERS, Unit::RADIANS, Unit::DEGREES])) {
            $earthRadius = Helpers::factors($units->value);
        } else {
            throw new InvalidArgumentException("Invalid units. Use 'kilometers', 'miles', 'degrees', or 'radians'.");
        }

        [$lon1, $lat1] = array_map('deg2rad', $point1);
        [$lon2, $lat2] = array_map('deg2rad', $point2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Interpolates a point between two points based on a fraction.
     *
     * @param  array  $start  The start point [lon, lat].
     * @param  array  $end  The end point [lon, lat].
     * @param  float  $fraction  The fraction along the segment (0 to 1).
     * @return array Interpolated point [lon, lat].
     */
    public function interpolatePoint(array $start, array $end, float $fraction): array
    {
        return [
            $start[0] + $fraction * ($end[0] - $start[0]),
            $start[1] + $fraction * ($end[1] - $start[1]),
        ];
    }
}
