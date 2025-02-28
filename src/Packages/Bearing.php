<?php

namespace Turf\Packages;

class Bearing
{
    /**
     * @param float[] $start
     * @param float[] $end
     * @param bool $final
     * @return float
     */
    public function __invoke(array $start, array $end, bool $final = false): float
    {
        // Convert coordinates to radians
        [$lon1, $lat1] = array_map('deg2rad', $start);
        [$lon2, $lat2] = array_map('deg2rad', $end);

        $dLon = $lon2 - $lon1;

        // Calculate initial bearing using spherical trigonometry
        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

        $initialBearing = rad2deg(atan2($y, $x));

        // Normalize bearing to [0, 360) to match Turf.js
        $normalizedBearing = fmod(($initialBearing + 360), 360);

        // Return final bearing if requested
        if ($final) {
            return self::calculateFinalBearing($start, $end);
        }

        return $normalizedBearing;
    }

    /**
     * Calculates the final bearing between two points.
     *
     * @param  float[]  $start  The starting point [lon, lat].
     * @param  float[]  $end  The ending point [lon, lat].
     * @return float The final bearing in degrees [0, 360).
     */
    private static function calculateFinalBearing(array $start, array $end): float
    {
        // Calculate reverse bearing (end to start)
        $reverseBearing = (new self)($end, $start);

        // Final bearing is reverse bearing + 180°, normalized to [0, 360)
        $finalBearing = fmod($reverseBearing + 180, 360);

        return $finalBearing;
    }
}
