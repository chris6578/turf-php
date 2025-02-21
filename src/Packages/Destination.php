<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Point;
use willvincent\Turf\Enums\Unit;

class Destination
{
    public function __invoke(
        array|Point $origin,
        float $distance,
        float $bearing,
        string|Unit $units = 'kilometers'
    ): Point {
        if (! $origin instanceof Point) {
            $origin = new Point($origin);
        }

        $coordinates1 = $origin->getCoordinates();
        $longitude1 = deg2rad($coordinates1[0]);
        $latitude1 = deg2rad($coordinates1[1]);
        $bearingRad = deg2rad($bearing);

        $radians = Helpers::lengthToRadians(
            distance: $distance,
            units: $units
        );

        $latitude2 = asin(sin($latitude1) * cos($radians) +
            cos($latitude1) * sin($radians) * cos($bearingRad));

        $longitude2 = $longitude1 +
            atan2(
                sin($bearingRad) * sin($radians) * cos($latitude1),
                cos($radians) - sin($latitude1) * sin($latitude2),
            );

        $lng = rad2deg($longitude2);
        $lat = rad2deg($latitude2);

        return new Point([$lng, $lat]);
    }
}
