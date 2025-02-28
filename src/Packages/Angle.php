<?php

namespace Turf\Packages;

use InvalidArgumentException;

class Angle
{
    public function __invoke(
        mixed $startPoint, // Remove array type hint
        mixed $midPoint,   // Remove array type hint
        mixed $endPoint,   // Remove array type hint
        bool $explementary = false,
        bool $mercator = false
    ): float {
        // Ensure input is valid
        if (! is_array($startPoint) || ! is_array($midPoint) || ! is_array($endPoint)) {
            throw new InvalidArgumentException('All points must be arrays with [lon, lat] format.');
        }

        // Compute azimuths
        $azimuthOA = self::bearingToAzimuth($mercator ? self::rhumbBearing($midPoint, $startPoint) : self::bearing($midPoint, $startPoint));
        $azimuthOB = self::bearingToAzimuth($mercator ? self::rhumbBearing($midPoint, $endPoint) : self::bearing($midPoint, $endPoint));

        // Calculate the smallest angle between the bearings
        $angleAOB = fmod($azimuthOB - $azimuthOA + 360, 360);
        $interiorAngle = min($angleAOB, 360 - $angleAOB);

        // Return explementary angle if requested, otherwise the interior angle
        return $explementary ? 360 - $interiorAngle : $interiorAngle;
    }

    /**
     * Converts a bearing to an azimuth (0 to 360 degrees).
     *
     * @param  float  $bearing  The bearing angle.
     * @return float The azimuth angle.
     */
    private static function bearingToAzimuth(float $bearing): float
    {
        return fmod(($bearing + 360), 360);
    }

    /**
     * Calculates the great-circle bearing between two points using Haversine formula.
     *
     * @param  float[]  $from  The starting point [lon, lat].
     * @param  float[]  $to  The ending point [lon, lat].
     * @return float The bearing in degrees.
     */
    private static function bearing(array $from, array $to): float
    {
        [$lon1, $lat1] = array_map('deg2rad', $from);
        [$lon2, $lat2] = array_map('deg2rad', $to);

        $dLon = $lon2 - $lon1;

        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

        return rad2deg(atan2($y, $x));
    }

    /**
     * Calculates the rhumb-line bearing between two points.
     *
     * @param  float[]  $from  The starting point [lon, lat].
     * @param  float[]  $to  The ending point [lon, lat].
     * @return float The rhumb-line bearing in degrees.
     */
    private static function rhumbBearing(array $from, array $to): float
    {
        [$lon1, $lat1] = array_map('deg2rad', $from);
        [$lon2, $lat2] = array_map('deg2rad', $to);

        $dLon = $lon2 - $lon1;
        $dPhi = log(tan(M_PI / 4 + $lat2 / 2) / tan(M_PI / 4 + $lat1 / 2));

        if (abs($dLon) > M_PI) {
            $dLon = ($dLon > 0) ? -(2 * M_PI - $dLon) : (2 * M_PI + $dLon);
        }

        return rad2deg(atan2($dLon, $dPhi));
    }
}
