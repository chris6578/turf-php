<?php

declare(strict_types=1);

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use Polyclip\Clipper;

class Union
{
    public function __invoke(
        Feature|FeatureCollection|Polygon|MultiPolygon $geometry1,
        Feature|FeatureCollection|Polygon|MultiPolygon $geometry2,
        array $properties = [],
    ): Feature
    {
        $union = Clipper::union($geometry1, $geometry2);
        return new Feature($union->getGeometry(), $properties);
    }
}
