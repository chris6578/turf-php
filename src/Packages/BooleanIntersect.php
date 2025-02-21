<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use willvincent\Turf\Turf;

class BooleanIntersect
{
    public function __invoke(Geometry $geometry1, Geometry $geometry2): bool
    {
        return ! Turf::booleanDisjoint($geometry1, $geometry2);
    }
}
