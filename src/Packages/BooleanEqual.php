<?php

namespace Turf\Packages;

use GeoJson\Geometry\Geometry;

class BooleanEqual
{
    public function __invoke(Geometry $feature1, Geometry $feature2, int $precision = 6): bool
    {
        if (get_class($feature1) !== get_class($feature2)) {
            return false;
        }

        $coords1 = self::roundCoordinates($feature1->getCoordinates(), $precision);
        $coords2 = self::roundCoordinates($feature2->getCoordinates(), $precision);

        return json_encode($coords1) === json_encode($coords2);
    }

    /**
     * Rounds coordinates to the specified precision.
     *
     * @param  mixed[]  $coords  Coordinates to round.
     * @param  int  $precision  Decimal precision.
     * @return mixed Rounded coordinates.
     */
    private static function roundCoordinates($coords, int $precision): mixed
    {
        if (is_array($coords)) {
            return array_map(static function ($coord) use ($precision) {
                return self::roundCoordinates($coord, $precision);
            }, $coords);
        } else {
            return round($coords, $precision);
        }
    }
}
