<?php

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
use Turf\Turf;

class Envelope
{
    public function __invoke(
        GeoJson $geoJson,
    ): Feature
    {
        return Turf::bboxPolygon(Turf::bbox($geoJson));
    }
}
