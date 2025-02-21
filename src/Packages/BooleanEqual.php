<?php

namespace willvincent\Turf\Packages;

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
     * @param  array  $coords  Coordinates to round.
     * @param  int  $precision  Decimal precision.
     * @return array Rounded coordinates.
     */
    private static function roundCoordinates(array $coords, int $precision): array
    {
        return array_map(static function ($coord) use ($precision) {
            if (is_array($coord[0])) {
                return self::roundCoordinates($coord, $precision);
            }

            return array_map(static fn ($val) => round($val, $precision), $coord);
        }, $coords);
    }
}
