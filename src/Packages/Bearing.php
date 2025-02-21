<?php

namespace willvincent\Turf\Packages;

class Bearing
{
    public function __invoke(array $start, array $end, bool $final = false): float
    {
        [$lon1, $lat1] = array_map('deg2rad', $start);
        [$lon2, $lat2] = array_map('deg2rad', $end);

        $dLon = $lon2 - $lon1;

        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

        $initialBearing = rad2deg(atan2($y, $x));

        // Normalize bearing to be between -180 and 180 degrees
        $normalizedBearing = fmod(($initialBearing + 360 + 180), 360) - 180;

        if ($final) {
            return self::calculateFinalBearing($start, $end);
        }

        // 6 digit precision to match TurfJS
        return $normalizedBearing;
    }

    /**
     * Calculates the final bearing between two points.
     *
     * @param  array  $start  The starting point [lon, lat].
     * @param  array  $end  The ending point [lon, lat].
     * @return float The final bearing in degrees (-180 to 180).
     */
    private static function calculateFinalBearing(array $start, array $end): float
    {
        $bearing = (new static)($end, $start);
        $finalBearing = fmod(($bearing + 180), 360) - 180;

        // 6 digit precision to match TurfJS
        return $finalBearing;
    }
}
