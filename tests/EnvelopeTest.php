<?php

namespace Turf\Tests;

use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\Point;
use PHPUnit\Framework\TestCase;
use Turf\Turf;

class EnvelopeTest extends TestCase
{
    public function test_envelope()
    {
        $featureCollection = new FeatureCollection([
            new Feature(new Point([-75.343, 39.984])),
            new Feature(new Point([-75.833, 39.284])),
            new Feature(new Point([-75.534, 39.123]))
        ]);

        $expected = GeoJson::jsonUnserialize(json_decode('{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[-75.833,39.123],[-75.343,39.123],[-75.343,39.984],[-75.833,39.984],[-75.833,39.123]]]},"properties":{}}'));

        $enveloped = Turf::envelope($featureCollection);
        $this->assertEquals($expected, $enveloped);
        $this->assertInstanceOf(Feature::class, $enveloped);
    }
}
