<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Enums\Unit;

class TurfCircle
{
    public function __invoke(
        array|Point $center,
        float $radius,
        int $steps = 64,
        string|Unit $units = Unit::KILOMETERS,
        array $properties = []
    ): Feature {
        if (! $units instanceof Unit) {
            $units = Unit::from($units);
        }

        $coordinates = [];

        for ($i = 0; $i < $steps; $i++) {
            $destination = (new TurfDestination)(
                origin: $center,
                distance: $radius,
                bearing: ($i * -360) / $steps,
                units: $units
            );
            $coordinates[] = $destination->getCoordinates();
        }

        $coordinates[] = $coordinates[0];

        return new Feature(
            geometry: new Polygon([$coordinates]),
            properties: $properties,
        );
    }
}
