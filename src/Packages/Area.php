<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\GeoJson;
use InvalidArgumentException;
use willvincent\Turf\Enums\Unit;

class Area
{
    const AREA_FACTOR = (Helpers::EARTH_RADIUS * Helpers::EARTH_RADIUS / 2);

    /**
     * Calculates the approximate area of a GeoJSON geometry (Polygon or MultiPolygon) in square meters.
     *
     * @param  GeoJson  $geoJSON  The GeoJSON object.
     * @return float The calculated area in square meters.
     */
    public function __invoke(GeoJson $geoJSON, ?string $units = 'meters'): float
    {
        $units = Unit::from($units);

        $totalArea = 0.0;

        switch ($geoJSON->getType()) {
            case 'Feature':
                $geo = $geoJSON->getGeometry();
                $totalArea = (new self)($geo);
                break;
            case 'FeatureCollection':
                $features = $geoJSON->getFeatures();
                foreach ($features as $feature) {
                    $totalArea += (new self)($feature);
                }
                break;
            case 'Polygon':
                $totalArea += self::calculatePolygonArea($geoJSON->getCoordinates());
                break;
            case 'MultiPolygon':
                foreach ($geoJSON->getCoordinates() as $polygon) {
                    $totalArea += self::calculatePolygonArea($polygon);
                }
                break;
            default:
                throw new InvalidArgumentException('Only Feature, FeatureCollection, Polygon and MultiPolygon types are supported');
        }

        return $totalArea * Helpers::areaFactors($units->value);
    }

    /**
     * Calculates the area of a single polygon using the spherical excess formula.
     *
     * @param  array  $coordinates  Polygon coordinates (including holes).
     * @return float The polygon area in square meters.
     */
    private static function calculatePolygonArea(array $coordinates): float
    {
        $totalArea = 0.0;

        foreach ($coordinates as $ringIndex => $ring) {
            $area = self::calculateRingArea($ring);
            if ($ringIndex === 0) {
                $totalArea += $area;
            } else {
                $totalArea -= $area;
            }
        }

        return $totalArea;
    }

    /**
     * Calculates the area of a linear ring using the spherical excess formula.
     *
     * @param  array  $ring  The linear ring coordinates.
     * @return float The area of the ring.
     */
    private static function calculateRingArea(array $ring): float
    {
        $ringCount = count($ring);
        if ($ringCount < 3) {
            return 0.0; // Invalid polygon
        }

        $area = 0.0;

        for ($i = 0; $i < $ringCount - 1; $i++) {
            [$lon1, $lat1] = $ring[$i];
            [$lon2, $lat2] = $ring[$i + 1];

            $lon1 = deg2rad($lon1);
            $lat1 = deg2rad($lat1);
            $lon2 = deg2rad($lon2);
            $lat2 = deg2rad($lat2);

            $area += ($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
        }

        $area = abs($area * self::AREA_FACTOR);

        return $area;
    }
}
