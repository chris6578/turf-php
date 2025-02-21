<?php

namespace willvincent\Turf\Enums;

use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;

enum LineGeometry: string
{
    case LINESTRING = LineString::class;
    case MULTILINESTRING = MultiLineString::class;
    case POLYGON = Polygon::class;
    case MULTIPOLYGON = MultiPolygon::class;
}
