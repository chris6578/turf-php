<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\GeoJson;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Feature\Feature;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;

class TurfClone
{
    public function __invoke(GeoJson $GeoJSON): GeoJson
    {
        switch ($GeoJSON->getType()) {
            case 'FeatureCollection':
                return new FeatureCollection(
                    $GeoJSON->getFeatures(),
                    $GeoJSON->getProperties()
                );
            case 'Feature':
                return new Feature(
                    $GeoJSON->getGeometry(),
                    $GeoJSON->getProperties()
                );
            case 'MultiPolygon':
                return new MultiPolygon(
                    $GeoJSON->getCoordinates(),
                );
            case 'Polygon':
                return new Polygon(
                    $GeoJSON->getCoordinates()
                );
            case 'Point':
                return new Point(
                    $GeoJSON->getCoordinates(),
                );
            case 'MultiPoint':
                return new MultiPoint(
                    $GeoJSON->getCoordinates(),
                );
            case 'LineString':
                return new MultiPoint(
                    $GeoJSON->getCoordinates(),
                );
            case 'MultiLineString':
                return new MultiLineString(
                    $GeoJSON->getCoordinates(),
                );
            case 'GeometryCollection':
                return new GeometryCollection(
                    $GeoJSON->getGeometries(),
                );
        }
    }
}
