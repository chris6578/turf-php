<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\Polygon;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class DifferenceTest extends TestCase
{
    public function test_simple_difference()
    {
        $geo1 = new Polygon([[[128, -26], [141, -26], [141, -21], [128, -21], [128, -26]]]);
        $geo2 = new Polygon([[[126, -28], [140, -28], [140, -20], [126, -20], [126, -28]]]);

        $expected = new Feature(
            new Polygon([[[140, -26], [140, -21], [141, -21], [141, -26], [140, -26]]]),
            []
        );

        $difference = Turf::difference($geo1, $geo2);
        $this->assertEquals($expected, $difference);
        $this->assertInstanceOf(Feature::class, $difference);
    }

    public function test_difference_multiple()
    {
        $geo1 = new Polygon([[[140, -26], [141, -26], [141, -21], [140, -21], [140, -26]]]);
        $geo2 = new FeatureCollection(GeoJson::jsonUnserialize(json_decode('{"type":"FeatureCollection","features":[{"type":"Feature","properties":{},"geometry":{"coordinates":[[[137.53839914218156,-23.471141163468843],[137.53839914218156,-24.09988275488074],[143.54861965904655,-24.09988275488074],[143.54861965904655,-23.471141163468843],[137.53839914218156,-23.471141163468843]]],"type":"Polygon"}},{"type":"Feature","properties":{},"geometry":{"coordinates":[[[139.31537433609464,-25.38385779295247],[139.31537433609464,-25.676028374359632],[141.92172900732083,-25.676028374359632],[141.92172900732083,-25.38385779295247],[139.31537433609464,-25.38385779295247]]],"type":"Polygon"}}]}'))->getFeatures());
        $expected = GeoJson::jsonUnserialize(json_decode('{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[140,-26],[140,-25.67602837436],[141,-25.67602837436],[141,-26],[140,-26]]],[[[140,-25.383857792952],[140,-24.099882754881],[141,-24.099882754881],[141,-25.383857792952],[140,-25.383857792952]]],[[[140,-23.471141163469],[140,-21],[141,-21],[141,-23.471141163469],[140,-23.471141163469]]]]},"properties":{}}'));

        $difference = Turf::difference($geo1, $geo2);
        $this->assertEquals($expected, $difference);
        $this->assertInstanceOf(Feature::class, $difference);
    }
}
