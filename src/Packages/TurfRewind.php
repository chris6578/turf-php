<?php

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Polygon;
use GeoJson\Geometry\MultiPolygon;
use InvalidArgumentException;

class TurfRewind
{
    public function __invoke(
        Polygon | MultiPolygon $geoJSON,
        bool $reverse = false,
    ): Polygon | MultiPolygon
    {
        switch ($geoJSON->getType()) {
            case 'Polygon':
                return new Polygon(self::rewindPolygon($geoJSON->getCoordinates(), $reverse));
            case 'MultiPolygon':
                $polygons = array_map(fn($polygon) => self::rewindPolygon($polygon, $reverse), $geoJSON->getCoordinates());
                return new MultiPolygon($polygons);
        }
        throw new InvalidArgumentException("Only Polygon or MultiPolygon can be rewound.");
    }

    private static function rewindPolygon(
        array $coordinates,
        bool $reverse
    ): array
    {
        foreach ($coordinates as $index => &$ring) {
            $isOuter = ($index === 0);
            $shouldBeCounterClockwise = $isOuter !== $reverse;

            if (self::isClockwise($ring) === $shouldBeCounterClockwise) {
                $ring = array_reverse($ring);
            }
        }

        return $coordinates;
    }

    private static function isClockwise(array $ring): bool
    {
        $sum = 0.0;
        $count = count($ring);

        for ($i = 0; $i < $count - 1; $i++) {
            [$x1, $y1] = $ring[$i];
            [$x2, $y2] = $ring[$i + 1];

            $sum += ($x2 - $x1) * ($y2 + $y1);
        }

        return $sum > 0;
    }

}
