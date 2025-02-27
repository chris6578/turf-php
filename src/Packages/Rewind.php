<?php

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;

class Rewind
{
    public function __invoke(
        GeoJson $geoJSON,
        bool $reverse = false,
    ): GeometryCollection|FeatureCollection|LineString|MultiLineString|Polygon|MultiPolygon|GeoJson {
        if ($geoJSON instanceof FeatureCollection) {
            $features = array_map(fn ($feature) => self::rewindFeature($feature, $reverse)->getGeometry(), $geoJSON->getFeatures());

            return new FeatureCollection($features);
        } elseif ($geoJSON instanceof GeometryCollection) {
            $geometries = array_map(fn ($geometry) => self::rewindFeature($geometry, $reverse)->getGeometry(), $geoJSON->getGeometries());

            return new GeometryCollection($geometries);
        } else {
            return self::rewindFeature($geoJSON, $reverse);
        }
    }

    private static function rewindFeature(
        GeoJson $geoJSON,
        bool $reverse
    ): Feature|LineString|MultiLineString|Polygon|MultiPolygon|Point|MultiPoint|GeoJson {
        if ($geoJSON instanceof Feature) {
            return new Feature(self::rewindFeature($geoJSON->getGeometry(), $reverse)->getGeometry());
        }

        switch (get_class($geoJSON)) {
            case LineString::class:
                return new LineString(self::rewindLineString($geoJSON->getCoordinates(), $reverse));
            case MultiLineString::class:
                return new MultiLineString(array_map(fn ($coords) => self::rewindLineString($coords, $reverse), $geoJSON->getCoordinates()));
            case Polygon::class:
                return new Polygon(self::rewindPolygon($geoJSON->getCoordinates(), $reverse));
            case MultiPolygon::class:
                return new MultiPolygon(array_map(fn ($coords) => self::rewindPolygon($coords, $reverse), $geoJSON->getCoordinates()));
            default:
                return $geoJSON; // Return as is for unsupported types (Point, MultiPoint)
        }
    }

    /**
     * @param mixed[] $coords
     * @param bool $reverse
     * @return mixed[]
     */
    private static function rewindLineString(array $coords, bool $reverse): array
    {
        if (self::isClockwise($coords) === $reverse) {
            return array_reverse($coords);
        }

        return $coords;
    }

    /**
     * @param mixed[] $coords
     * @param bool $reverse
     * @return mixed[]
     */
    private static function rewindPolygon(array $coords, bool $reverse): array
    {
        // Ensure outer ring is counterclockwise
        if (self::isClockwise($coords[0]) !== $reverse) {
            $coords[0] = array_reverse($coords[0]);
        }

        // Ensure inner rings are clockwise
        for ($i = 1; $i < count($coords); $i++) {
            if (self::isClockwise($coords[$i]) === $reverse) {
                $coords[$i] = array_reverse($coords[$i]);
            }
        }

        return $coords;
    }

    /**
     * @param mixed[] $ring
     * @return bool
     */
    private static function isClockwise(array $ring): bool
    {
        return (new BooleanClockwise)($ring);
    }
}
