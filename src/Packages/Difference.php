<?php

declare(strict_types=1);

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use Polyclip\Clipper;

class Difference
{
    /**
     * @param Feature|FeatureCollection|Polygon|MultiPolygon $geometry1
     * @param Feature|FeatureCollection|Polygon|MultiPolygon $geometry2
     * @param mixed[] $properties
     * @return Feature
     */
    public function __invoke(
        Feature|FeatureCollection|Polygon|MultiPolygon $geometry1,
        Feature|FeatureCollection|Polygon|MultiPolygon $geometry2,
        array $properties = [],
    ): Feature
    {
        $difference = Clipper::difference($geometry1, $geometry2);
        return new Feature($difference->getGeometry(), $properties);
    }
}
