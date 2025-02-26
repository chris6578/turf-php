<?php

namespace willvincent\Turf\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use willvincent\Turf\Enums\Unit;

class EnumsTest extends TestCase
{
    public function test_unit_cases(): void
    {
        $expectedUnits = [
            'meters' => Unit::METERS,
            'metres' => Unit::METRES,
            'millimeters' => Unit::MILLIMETERS,
            'millimetres' => Unit::MILLIMETRES,
            'centimeters' => Unit::CENTIMETERS,
            'centimetres' => Unit::CENTIMETRES,
            'kilometers' => Unit::KILOMETERS,
            'kilometres' => Unit::KILOMETRES,
            'miles' => Unit::MILES,
            'nauticalmiles' => Unit::NAUTICAL_MILES,
            'inches' => Unit::INCHES,
            'yards' => Unit::YARDS,
            'feet' => Unit::FEET,
            'radians' => Unit::RADIANS,
            'degrees' => Unit::DEGREES,
        ];

        foreach ($expectedUnits as $value => $unit) {
            $this->assertSame($value, $unit->value, "Unit::$unit->name should have value '$value'");
        }

        $this->assertCount(15, Unit::cases(), 'There should be exactly 15 unit cases defined');
    }

    public function test_unit_from_string_valid(): void
    {
        $this->assertSame(Unit::METERS, Unit::from('meters'), 'String "meters" should map to Unit::METERS');
        $this->assertSame(Unit::KILOMETRES, Unit::from('kilometres'), 'String "kilometres" should map to Unit::KILOMETRES');
        $this->assertSame(Unit::DEGREES, Unit::from('degrees'), 'String "degrees" should map to Unit::DEGREES');
    }

    public function test_unit_from_string_invalid(): void
    {
        $this->expectException(\ValueError::class);
        Unit::from('invalid');
    }

    public function test_unit_string_conversion(): void
    {
        $this->assertSame('meters', Unit::METERS->value, 'Unit::METERS should convert to "meters"');
        $this->assertSame('nauticalmiles', Unit::NAUTICAL_MILES->value, 'Unit::NAUTICAL_MILES should convert to "nauticalmiles"');
    }
}
