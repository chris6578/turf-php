# Turf-PHP

![Turf-PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)
![License](https://img.shields.io/github/license/willvincent/turf-php)

Turf-PHP is a PHP port of [**Turf.js**](https://turfjs.org), a powerful spatial analysis library.
This package provides robust GeoJSON-based geospatial functions for use in PHP applications, allowing you
to perform geometry operations, boolean calculations, and spatial analysis with ease.

### NOTE: This is a very early effort to port functionality!
It is incomplete and there should be a reasonable expectation that some output may differ slightly from
Turf.js, and some may be outright broken. If you need a feature that's not available, or not working
correctly, please consider opening a pull request!


## 🚀 Features

- Supports **PHP 8.2+**
- Implements key **Turf.js** functions
- Uses **GeoJSON** as the standard input and output format, leveraging the [jmikola/geojson](https://github.com/jmikola/geojson) package. 
- Includes **boolean operations**, **spatial analysis**, and **geometry transformations**

## 📦 Installation

For the moment, you have to install from this repo. It will be published and accessible via composer in the near future.

[//]: # ()
[//]: # (Install Turf-PHP using **Composer**:)

[//]: # ()
[//]: # (```sh)

[//]: # (composer require willvincent/turf-php)

[//]: # (```)

## 🛠 Usage

Turf-PHP provides various functions to work with geospatial data. Below are some examples of how to use it
in your PHP application.

### Example: Calculating the area of a FeatureCollection

```php
use GeoJson\GeoJson;
use willvincent\Turf\Turf;

$featureCollection = GeoJson::jsonUnserialize(json_decode('
    {"type":"FeatureCollection","features":[{"type":"Feature","properties":{"name":"LAX"},
     "geometry":{"coordinates":[[[-118.43130558364376,33.94968604365768],[-118.4012727129631,33.952569329158464],
     [-118.40029702645336,33.951330030271265],[-118.39709555509219,33.95155765795056],
     [-118.39679065305795,33.949812496849574],[-118.39974820279133,33.948750207354195],
     [-118.39996163421532,33.945689727778216],[-118.40846840097439,33.94498150194964],
     [-118.40798055771938,33.9421738344311],[-118.39718702570275,33.9431350283305],
     [-118.39419898576588,33.94450091678105],[-118.39133290664276,33.944804444571744],
     [-118.39079018485972,33.94308612928623],[-118.37905145653627,33.94394613617513],
     [-118.37889900551932,33.93582631317177],[-118.37908199117742,33.93213293103197],
     [-118.39511983818524,33.932006442427294],[-118.41701182370598,33.93172817593698],
     [-118.42484782241374,33.9317281792144],[-118.42856762723284,33.93134871103278],
     [-118.4328667459175,33.93914011860488],[-118.43561083211533,33.94422979717065],
     [-118.43536691048782,33.94683505044485],[-118.43152514485466,33.94772031253221],
     [-118.43130558364376,33.94968604365768]]],"type":"Polygon"}}]}
'));

// Default is Square Meters, but you can easily
// request a different unit of measure with a second parameter
$area = Turf::area($featureCollection);

$square_miles = Turf::area($featureCollection, 'miles');
```

### Example: Checking if a Point is in a Polygon

```php
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Turf;

$point = new Point([-77, 44]);
$polygon = new Polygon([
    [[-81, 41], [-81, 47], [-72, 47], [-72, 41], [-81, 41]]
]);

echo Turf::booleanPointInPolygon($point, $polygon) ? 'true' : 'false'; // Output: true
```

### Example: Generating a square grid

```php
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Polygon;
use willvincent\Turf\Turf;

$polygon1 = new Polygon([
  [
    [-87.62411094338104, 41.880714036293114],
    [-87.62411094338104, 41.87843046431638],
    [-87.6209051112661, 41.87843046431638],
    [-87.6209051112661, 41.880714036293114],
    [-87.62411094338104, 41.880714036293114]
  ]
]);
$polygon2 = new Polygon([
  [
    [-87.61655715285198, 41.884051046913896],
    [-87.61541915184196, 41.884164700799886],
    [-87.61534976153654, 41.88380307409719],
    [-87.61648776254657, 41.88369975180643],
    [-87.61655715285198, 41.884051046913896]
  ]
]);

$featureCollection = new FeatureCollection([
  new Feature($polygon1),
  new Feature($polygon2)
]);

// Draw a bounding box around our feature collection
$bbox = Turf::bbox($featureCollection);

// Generate a grid from the bounding box
$grid = Turf::squareGrid($bbox, 0.25, 'kilometers');
```

## 📚 Supported Functions

The following spatial analysis functions are currently implemented:

- ✅ `along` - Takes a LineString and returns a Point at a specified distance along the line.
- ✅ `angle` - Finds the angle formed by two adjacent segments defined by 3 points.
- ✅ `area` - Calculates the geodesic area in square meters (or other units) of one or more polygons.
- ✅ `bbox` - Calculates the bounding box for any GeoJSON object, including FeatureCollection
- ✅ `bearing` - Takes two points and finds the geographic bearing between them (NOTE: Output varies slightly from TurfJS calculations, presumably precision differences)
- ✅ `distance` - Calculate the distance between two points

The following generative and manipulation functions are currently implemented:

- ✅ `bboxClip` - Clips a feature to the bbox
- ✅ `bboxPolygon` - Takes a bbox and returns an equivalent polygon.
- ✅ `circle` - Calculates a circle of a given radius around a center point
- ✅ `destination` - Calculate the location of a destination point from an origin point
- ✅ `rectangleGrip` - Generates a rectangle grid within a bounding box
- ✅ `rewind` - Rewinds geometry to adhere to geojson spec
- ✅ `squareGrid` - Generates a square gris within a bounding box
- ✅ `kinks` - Finds all self-intersecting points
- ✅ `unkink` - Breaks self-intersecting polygons into separate polygons at the intersection points

The following boolean operations are currently implemented:
- ✅ `booleanClockwise`
- ✅ `booleanConcave`
- ✅ `booleanContains`
- ✅ `booleanCrosses`
- ✅ `booleanDisjoint`
- ✅ `booleanEqual`
- ✅ `booleanIntersects`
- ✅ `booleanOverlap`
- ✅ `booleanParallel`
- ✅ `booleanPointInPolygon`
- ✅ `booleanTouches`
- ✅ `booleanValid`
- ✅ `booleanWithin`

More functions will be added over time to expand the capabilities of **Turf-PHP**.

Testing and contributions are welcome!

## 📖 Documentation

Full documentation for Turf-PHP is pending! In the meantime, you can look at the `src/Turf.php` file for detail on
properties for each implemented function.

## 🤝 Contributing

Contributions are welcome! If you'd like to improve the library, add new functions, or fix bugs, feel free to
submit a pull request or open an issue.

## 📄 License

Turf-PHP is open-source and released under the **MIT License**.

---

🚀 **Turf-PHP** is a powerful tool for geospatial analysis in PHP. Start building today and bring the power of
Turf.js to your PHP applications!

