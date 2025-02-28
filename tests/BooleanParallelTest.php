<?php

namespace Turf\Tests;

use GeoJson\Geometry\LineString;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class BooleanParallelTest extends TestCase
{
    public function test_parallel_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 0]]);
        $line2 = new LineString([[0, 1], [1, 1]]);
        $this->assertTrue(
            Turf::booleanParallel($line1, $line2),
            'Parallel horizontal lines should return true'
        );
    }

    public function test_non_parallel_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 1]]);
        $line2 = new LineString([[0, 1], [1, 0]]);
        $this->assertFalse(
            Turf::booleanParallel($line1, $line2),
            'Non-parallel lines should return false'
        );
    }

    public function test_identical_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 1]]);
        $line2 = new LineString([[0, 0], [1, 1]]);
        $this->assertTrue(
            Turf::booleanParallel($line1, $line2),
            'Identical lines should be parallel'
        );
    }

    public function test_intersecting_lines(): void
    {
        $line1 = new LineString([[0, 0], [2, 2]]);
        $line2 = new LineString([[0, 2], [2, 0]]);
        $this->assertFalse(
            Turf::booleanParallel($line1, $line2),
            'Intersecting lines should not be parallel'
        );
    }

    public function test_parallel_multi_segment_lines(): void
    {
        $line1 = new LineString([[0, 0], [1, 0], [2, 0]]);
        $line2 = new LineString([[0, 1], [1, 1]]);
        $this->assertTrue(
            Turf::booleanParallel($line1, $line2),
            'Multi-segment parallel lines should return true'
        );
    }
}
