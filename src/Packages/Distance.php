<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\Point;
use willvincent\Turf\Enums\Unit;

class Distance
{
    /**
     * @param float[]|Point $from
     * @param float[]|Point $to
     * @param string|Unit $units
     * @return float
     */
    public function __invoke(
        array|Point $from,
        array|Point $to,
        string|Unit $units = Unit::KILOMETERS,
    ): float {
        if (! $from instanceof Geometry) {
            $from = new Point($from);
        }
        if (! $to instanceof Geometry) {
            $to = new Point($to);
        }
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }

        $coordinates1 = $from->getCoordinates();
        $coordinates2 = $to->getCoordinates();

        $dLat = deg2rad($coordinates2[1] - $coordinates1[1]);
        $dLon = deg2rad($coordinates2[0] - $coordinates1[0]);
        $lat1 = deg2rad($coordinates1[1]);
        $lat2 = deg2rad($coordinates2[1]);

        $a = pow(sin($dLat / 2), 2) +
            pow(sin($dLon / 2), 2) *
            cos($lat1) * cos($lat2);

        return Helpers::radiansToLength(
            radians: 2 * atan2(sqrt($a), sqrt(1 - $a)),
            units: $units,
        );
    }
}
