<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Turf\Enums\Unit;
use Turf\Packages\Helpers;
use Turf\Turf;

class HelpersTest extends TestCase
{
    public function test_convert_to_meters(): void
    {
        $this->assertEqualsWithDelta(1000, Helpers::convertToMeters(1, Unit::KILOMETERS), 0.001, '1 kilometer should be 1000 meters');
        $this->assertEqualsWithDelta(0.3048, Helpers::convertToMeters(1, Unit::FEET), 0.001, '1 foot should be 0.3048 meters');
        $this->assertEqualsWithDelta(1, Helpers::convertToMeters(1, Unit::METERS), 0.001, '1 meter should be 1 meter');
        $this->assertEqualsWithDelta(1609.34, Helpers::convertToMeters(1, Unit::MILES), 0.01, '1 mile should be ~1609.34 meters');
    }

    public function test_meters_to_degrees_latitude(): void
    {
        $meters = 111319.9; // Approx 1 degree at equator
        $degrees = Helpers::metersToDegreesLatitude($meters);
        $this->assertEqualsWithDelta(1.0, $degrees, 0.01, '111319.9 meters should be ~1 degree latitude');
    }

    public function test_meters_to_degrees_longitude(): void
    {
        $meters = 111319.9; // 1 degree longitude at equator
        $degrees = Helpers::metersToDegreesLongitude($meters, 0); // Equator
        $this->assertEqualsWithDelta(1.0, $degrees, 0.01, '111319.9 meters at equator should be ~1 degree longitude');

        $degreesAt45 = Helpers::metersToDegreesLongitude($meters, 45); // 45° latitude
        $this->assertGreaterThan(1.0, $degreesAt45, 'Longitude degrees should increase at higher latitudes');
    }

    public function test_factors(): void
    {
        $factors = Helpers::factors();
        $this->assertIsArray($factors, 'Factors should return an array when no argument is given');
        $this->assertEquals(Helpers::EARTH_RADIUS, Helpers::factors('meters'), 'Meters factor should equal EARTH_RADIUS');
        $this->assertEqualsWithDelta(Helpers::EARTH_RADIUS / 1609.344, Helpers::factors('miles'), 0.001, 'Miles factor should be correct');
    }

    public function test_area_factors(): void
    {
        $areaFactors = Helpers::areaFactors();
        $this->assertIsArray($areaFactors, 'Area factors should return an array when no argument is given');
        $this->assertEquals(1, Helpers::areaFactors('meters'), 'Meters area factor should be 1');
        $this->assertEquals(0.000001, Helpers::areaFactors('kilometers'), 'Kilometers area factor should be 0.000001');
    }

    public function test_length_to_radians(): void
    {
        $distance = 1; // 1 km
        $radians = Helpers::lengthToRadians($distance, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(1000 / Helpers::EARTH_RADIUS, $radians, 0.0001, '1 km should convert to correct radians');

        $degrees = 1;
        $radiansFromDegrees = Helpers::lengthToRadians($degrees, Unit::DEGREES);
        $this->assertEqualsWithDelta(deg2rad(1), $radiansFromDegrees, 0.0001, '1 degree should convert to correct radians');
    }

    public function test_radians_to_length(): void
    {
        $radians = 1000 / Helpers::EARTH_RADIUS;
        $length = Helpers::radiansToLength($radians, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(1, $length, 0.001, 'Radians should convert back to 1000 km');
    }

    public function test_compare_coords(): void
    {
        $this->assertTrue(Helpers::compareCoords([0, 0], [0, 0]), 'Identical coordinates should return true');
        $this->assertFalse(Helpers::compareCoords([0, 0], [1, 0]), 'Different coordinates should return false');
    }

    public function test_filter_grid_by_mask(): void
    {
        $grid = [
            new Feature(new Polygon([[[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]]])),
            new Feature(new Polygon([[[1, 1], [1.5, 1], [1.5, 1.5], [1, 1.5], [1, 1]]])),
        ];
        $mask = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $filtered = Helpers::filterGridByMask($grid, $mask);
        $this->assertCount(1, $filtered, 'Only one grid cell should overlap with mask');
        $this->assertEquals([0, 0], $filtered[0]->getGeometry()->getCoordinates()[0][0], 'Filtered cell should be the one at [0, 0]');
    }

    public function test_filter_grid_by_mask_feature_collection(): void
    {
        $grid = [
            new Feature(new Polygon([[[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]]])),
            new Feature(new Polygon([[[1, 1], [1.5, 1], [1.5, 1.5], [1, 1.5], [1, 1]]])),
        ];
        $mask = new FeatureCollection([new Feature(new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]))]);
        $filtered = Helpers::filterGridByMask($grid, $mask);
        $this->assertCount(1, $filtered, 'FeatureCollection mask should filter correctly');
    }

    public function test_haversine_distance(): void
    {
        $distance = Helpers::haversineDistance([0, 0], [0, 1], Unit::KILOMETERS);
        $this->assertEqualsWithDelta(111.32, $distance, 0.2, 'Haversine distance along meridian should be ~111.32 km');

        $this->expectException(InvalidArgumentException::class);
        Helpers::haversineDistance([0, 0], [0, 1], Unit::METERS); // Invalid unit for haversine
    }

    public function test_converts_to_meters_correctly(): void
    {
        $this->assertEqualsWithDelta(1000, Helpers::convertToMeters(1, Unit::KILOMETERS), 0.001, '1 kilometer should be 1000 meters');
        $this->assertEqualsWithDelta(0.3048, Helpers::convertToMeters(1, Unit::FEET), 0.001, '1 foot should be 0.3048 meters');
        $this->assertEqualsWithDelta(1, Helpers::convertToMeters(1, Unit::METERS), 0.001, '1 meter should be 1 meter');
        $this->assertEqualsWithDelta(1609.34, Helpers::convertToMeters(1, Unit::MILES), 0.01, '1 mile should be ~1609.34 meters');
        $this->assertEqualsWithDelta(0, Helpers::convertToMeters(0, Unit::KILOMETERS), 0.001, '0 kilometers should be 0 meters');
    }

    public function test_converts_meters_to_degrees_latitude(): void
    {
        $meters = 111319.9; // Approx 1 degree latitude at equator
        $degrees = Helpers::metersToDegreesLatitude($meters);
        $this->assertEqualsWithDelta(1.0, $degrees, 0.01, '111319.9 meters should be ~1 degree latitude');
        $this->assertEqualsWithDelta(0, Helpers::metersToDegreesLatitude(0), 0.001, '0 meters should be 0 degrees');
    }

    public function test_converts_meters_to_degrees_longitude(): void
    {
        $meters = 111319.9; // 1 degree longitude at equator
        $degreesAtEquator = Helpers::metersToDegreesLongitude($meters, 0); // Equator
        $this->assertEqualsWithDelta(1.0, $degreesAtEquator, 0.01, '111319.9 meters at equator should be ~1 degree longitude');

        $degreesAt45 = Helpers::metersToDegreesLongitude($meters, 45); // 45° latitude
        $this->assertGreaterThan(1.0, $degreesAt45, 'Longitude degrees should increase at higher latitudes');
        $this->assertEqualsWithDelta(0, Helpers::metersToDegreesLongitude(0, 45), 0.001, '0 meters should be 0 degrees');
    }

    public function test_returns_factors_correctly(): void
    {
        $factors = Helpers::factors();
        $this->assertIsArray($factors, 'Factors should return an array when no argument is given');
        $this->assertEquals(Helpers::EARTH_RADIUS, Helpers::factors('meters'), 'Meters factor should equal EARTH_RADIUS');
        $this->assertEqualsWithDelta(Helpers::EARTH_RADIUS / 1609.344, Helpers::factors('miles'), 0.001, 'Miles factor should be correct');
        $this->assertEqualsWithDelta(360 / (2 * M_PI), Helpers::factors('degrees'), 0.001, 'Degrees factor should be correct');
    }

    public function test_returns_area_factors_correctly(): void
    {
        $areaFactors = Helpers::areaFactors();
        $this->assertIsArray($areaFactors, 'Area factors should return an array when no argument is given');
        $this->assertEquals(1, Helpers::areaFactors('meters'), 'Meters area factor should be 1');
        $this->assertEquals(0.000001, Helpers::areaFactors('kilometers'), 'Kilometers area factor should be 0.000001');
        $this->assertEqualsWithDelta(0.000247105, Helpers::areaFactors('acres'), 0.000001, 'Acres area factor should be correct');
    }

    public function test_converts_length_to_radians(): void
    {
        $distance = 1; // 1 km
        $radians = Helpers::lengthToRadians($distance, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(1000 / Helpers::EARTH_RADIUS, $radians, 0.0001, '1 km should convert to correct radians');

        $degrees = 1;
        $radiansFromDegrees = Helpers::lengthToRadians($degrees, Unit::DEGREES);
        $this->assertEqualsWithDelta(deg2rad(1), $radiansFromDegrees, 0.0001, '1 degree should convert to correct radians');
        $this->assertEqualsWithDelta(0, Helpers::lengthToRadians(0, Unit::KILOMETERS), 0.0001, '0 km should be 0 radians');
    }

    public function test_converts_radians_to_length(): void
    {
        $radians = 1000 / Helpers::EARTH_RADIUS;
        $length = Helpers::radiansToLength($radians, Unit::KILOMETERS);
        $this->assertEqualsWithDelta(1, $length, 0.001, 'Radians should convert back to 1 km');
        $this->assertEqualsWithDelta(0, Helpers::radiansToLength(0, Unit::KILOMETERS), 0.001, '0 radians should be 0 km');
    }

    public function test_compares_coordinates_correctly(): void
    {
        $this->assertTrue(Helpers::compareCoords([0, 0], [0, 0]), 'Identical coordinates should return true');
        $this->assertFalse(Helpers::compareCoords([0, 0], [1, 0]), 'Different coordinates should return false');
        $this->assertFalse(Helpers::compareCoords([0, 1], [0, 0]), 'Coordinates differing in one dimension should return false');
    }

    public function test_filters_grid_by_mask_with_polygon(): void
    {
        $grid = [
            new Feature(new Polygon([[[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]]])),
            new Feature(new Polygon([[[1, 1], [1.5, 1], [1.5, 1.5], [1, 1.5], [1, 1]]])),
        ];
        $mask = new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]);
        $filtered = Helpers::filterGridByMask($grid, $mask);

        $this->assertCount(1, $filtered, 'Only one grid cell should overlap with mask');
        $this->assertEquals([0, 0], $filtered[0]->getGeometry()->getCoordinates()[0][0], 'Filtered cell should be the one at [0, 0]');
    }

    public function test_filters_grid_by_mask_with_feature_collection(): void
    {
        $grid = [
            new Feature(new Polygon([[[0, 0], [0.5, 0], [0.5, 0.5], [0, 0.5], [0, 0]]])),
            new Feature(new Polygon([[[1, 1], [1.5, 1], [1.5, 1.5], [1, 1.5], [1, 1]]])),
        ];
        $mask = new FeatureCollection([new Feature(new Polygon([[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]))]);
        $filtered = Helpers::filterGridByMask($grid, $mask);

        $this->assertCount(1, $filtered, 'FeatureCollection mask should filter correctly');
        $this->assertEquals([0, 0], $filtered[0]->getGeometry()->getCoordinates()[0][0], 'Filtered cell should be the one at [0, 0]');
    }

    public function test_calculates_haversine_distance_correctly(): void
    {
        $distance = Helpers::haversineDistance([0, 0], [0, 1], Unit::KILOMETERS);
        $this->assertEqualsWithDelta(111.32, $distance, 0.2, 'Haversine distance along meridian should be ~111.32 km');

        $distanceZero = Helpers::haversineDistance([0, 0], [0, 0], Unit::KILOMETERS);
        $this->assertEqualsWithDelta(0, $distanceZero, 0.001, 'Distance between identical points should be 0');
    }

    public function test_throws_exception_for_invalid_haversine_units(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid units. Use 'kilometers', 'miles', 'degrees', or 'radians'.");
        Helpers::haversineDistance([0, 0], [0, 1], Unit::METERS); // Invalid unit for haversine
    }
}
