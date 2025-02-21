<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;

class BooleanPointInPolygon
{
    public function __invoke(Point $point, Polygon|MultiPolygon $polygon, bool $ignoreBoundary = false): bool
    {
        $pt = $point->getCoordinates();
        $polys = $polygon instanceof Polygon ? [$polygon->getCoordinates()] : $polygon->getCoordinates();

        foreach ($polys as $poly) {
            if (self::isPointInPolygon($pt, $poly, $ignoreBoundary)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a point is inside a single polygon.
     *
     * @param  array  $point  The point coordinates.
     * @param  array  $polygon  The polygon coordinates.
     * @param  bool  $ignoreBoundary  Whether to ignore points on the boundary.
     * @return bool True if inside, false otherwise.
     */
    private static function isPointInPolygon(array $point, array $polygon, bool $ignoreBoundary): bool
    {
        $inside = false;
        $j = count($polygon[0]) - 1;
        for ($i = 0; $i < count($polygon[0]); $i++) {
            $xi = $polygon[0][$i][0];
            $yi = $polygon[0][$i][1];
            $xj = $polygon[0][$j][0];
            $yj = $polygon[0][$j][1];

            $intersect = (($yi > $point[1]) !== ($yj > $point[1])) &&
                ($point[0] < ($xj - $xi) * ($point[1] - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = ! $inside;
            }
            $j = $i;
        }

        if ($ignoreBoundary && self::isPointOnBoundary($point, $polygon)) {
            return false;
        }

        return $inside;
    }

    /**
     * Checks if a point is on the boundary of a polygon.
     *
     * @param  array  $point  The point coordinates.
     * @param  array  $polygon  The polygon coordinates.
     * @return bool True if on boundary, false otherwise.
     */
    private static function isPointOnBoundary(array $point, array $polygon): bool
    {
        foreach ($polygon[0] as $i => $coord) {
            if ($i < count($polygon[0]) - 1) {
                if (Helpers::isPointOnLineSegment($polygon[0][$i], $polygon[0][$i + 1], $point)) {
                    return true;
                }
            }
        }

        return false;
    }
}
