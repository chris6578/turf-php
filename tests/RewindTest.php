<?php

namespace Turf\Tests;

use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class RewindTest extends TestCase
{
    public function test_rewind_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]); // Clockwise
        $rewound = Turf::rewind($polygon);
        $coords = $rewound->getCoordinates()[0];

        $this->assertEquals(
            [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]],
            $coords,
            'Polygon should be rewound to counterclockwise'
        );
    }

    public function test_properly_wound_polygon(): void
    {
        $polygon = new Polygon([[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]]); // Counter-Clockwise
        $rewound = Turf::rewind($polygon);
        $coords = $rewound->getCoordinates()[0];

        $this->assertEquals(
            [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]],
            $coords,
            'Polygon should be unchanged'
        );
    }

    public function test_rewind_with_hole(): void
    {
        $polygon = new Polygon([
            [[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]], // Outer clockwise
            [[0.5, 0.5], [0.5, 1.5], [1.5, 1.5], [1.5, 0.5], [0.5, 0.5]], // Inner counterclockwise
        ]);
        $rewound = Turf::rewind($polygon);
        $outer = $rewound->getCoordinates()[0];
        $inner = $rewound->getCoordinates()[1];

        $this->assertFalse(
            Turf::booleanClockwise($outer),
            'Outer ring should be counterclockwise after rewind'
        );
        $this->assertTrue(
            Turf::booleanClockwise($inner),
            'Inner ring should be clockwise after rewind'
        );
    }

    public function test_rewind_reverse(): void
    {
        $polygon = new Polygon([[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]]); // Counterclockwise
        $rewound = Turf::rewind($polygon, true);
        $coords = $rewound->getCoordinates()[0];

        $this->assertTrue(
            Turf::booleanClockwise($coords),
            'Polygon should be rewound to clockwise with reverse=true'
        );
    }
}
