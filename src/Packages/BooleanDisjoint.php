<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanDisjoint
{
    public function __invoke(Geometry $feature1, Geometry $feature2): bool
    {
        if ($feature1 instanceof Point && $feature2 instanceof Point) {
            return ! Helpers::compareCoords($feature1->getCoordinates(), $feature2->getCoordinates());
        }

        if ($feature1 instanceof Point && $feature2 instanceof LineString) {
            return ! Turf::booleanPointOnLine($feature1, $feature2);
        }

        if ($feature1 instanceof Point && $feature2 instanceof Polygon) {
            return ! Turf::booleanPointInPolygon($feature1, $feature2);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof Point) {
            return ! Turf::booleanPointOnLine($feature2, $feature1);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof LineString) {
            return ! Turf::booleanIntersect($feature1, $feature2);
        }

        if ($feature1 instanceof LineString && $feature2 instanceof Polygon) {
            return ! Turf::booleanWithin($feature1, $feature2);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof Point) {
            return ! Turf::booleanPointInPolygon($feature2, $feature1);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof LineString) {
            return ! Turf::booleanWithin($feature2, $feature1);
        }

        if ($feature1 instanceof Polygon && $feature2 instanceof Polygon) {
            return ! Turf::booleanWithin($feature1, $feature2);
        }

        throw new InvalidArgumentException('Unsupported geometry types');
    }
}
