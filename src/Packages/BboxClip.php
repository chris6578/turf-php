<?php

namespace willvincent\Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class BboxClip
{
    public function __invoke(
        GeoJson $feature,
        array $bbox
    ): Feature {
        if (count($bbox) !== 4) {
            throw new InvalidArgumentException('BBox must have exactly 4 values: [minX, minY, maxX, maxY]');
        }

        [$minX, $minY, $maxX, $maxY] = $bbox;

        if ($feature instanceof Feature) {
            $geometry = $feature->getGeometry();
        } else {
            $geometry = $feature;
        }

        switch (get_class($geometry)) {
            case LineString::class:
                return new Feature(new LineString(self::clipLine($geometry->getCoordinates(), $bbox)));
            case MultiLineString::class:
                $clippedLines = array_map(fn ($line) => self::clipLine($line, $bbox), $geometry->getCoordinates());

                return new Feature(new MultiLineString(array_filter($clippedLines, fn ($line) => ! empty($line))));
            case Polygon::class:
                return new Feature(new Polygon(self::clipPolygon($geometry->getCoordinates(), $bbox)));
            case MultiPolygon::class:
                $clippedPolygons = array_map(fn ($poly) => self::clipPolygon($poly, $bbox), $geometry->getCoordinates());

                return new Feature(new MultiPolygon(array_filter($clippedPolygons, fn ($poly) => ! empty($poly))));
            default:
                throw new InvalidArgumentException('Geometry type not supported for bbox clipping.');
        }
    }

    /**
     * Clips a LineString using the Cohen-Sutherland algorithm.
     *
     * @param  array  $line  The line coordinates.
     * @param  array  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return array The clipped line coordinates.
     */
    private static function clipLine(array $line, array $bbox): array
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;
        $clipped = [];

        for ($i = 0; $i < count($line) - 1; $i++) {
            $p1 = $line[$i];
            $p2 = $line[$i + 1];

            $clippedSegment = self::cohenSutherlandClip($p1, $p2, $bbox);
            if (! empty($clippedSegment)) {
                $clipped = array_merge($clipped, $clippedSegment);
            }
        }

        return $clipped;
    }

    /**
     * Clips a Polygon using the Sutherland-Hodgman algorithm.
     *
     * @param  array  $rings  The polygon rings.
     * @param  array  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return array The clipped polygon rings.
     */
    private static function clipPolygon(array $rings, array $bbox): array
    {
        $clippedRings = [];

        foreach ($rings as $ring) {
            $clipped = self::sutherlandHodgmanClip($ring, $bbox);
            if (count($clipped) >= 4) { // Ensure valid polygon
                $clippedRings[] = $clipped;
            }
        }

        return $clippedRings;
    }

    /**
     * Performs Cohen-Sutherland line clipping algorithm.
     *
     * @param  array  $p1  First point [x, y].
     * @param  array  $p2  Second point [x, y].
     * @param  array  $bbox  Bounding box [minX, minY, maxX, maxY].
     * @return array|null Clipped line segment or null if outside.
     */
    private static function cohenSutherlandClip(array $p1, array $p2, array $bbox): ?array
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;

        $code = function ($point) use ($minX, $minY, $maxX, $maxY) {
            [$x, $y] = $point;
            $code = 0;
            if ($x < $minX) {
                $code |= 1;
            } // Left
            if ($x > $maxX) {
                $code |= 2;
            } // Right
            if ($y < $minY) {
                $code |= 4;
            } // Bottom
            if ($y > $maxY) {
                $code |= 8;
            } // Top

            return $code;
        };

        $c1 = $code($p1);
        $c2 = $code($p2);

        while (true) {
            if (($c1 | $c2) === 0) {
                return [$p1, $p2]; // Fully inside
            }
            if (($c1 & $c2) !== 0) {
                return []; // Fully outside
            }

            $cOut = $c1 ?: $c2;
            [$x, $y] = $p1;

            if ($cOut & 8) { // Top
                $x = $p1[0] + ($p2[0] - $p1[0]) * ($maxY - $p1[1]) / ($p2[1] - $p1[1]);
                $y = $maxY;
            } elseif ($cOut & 4) { // Bottom
                $x = $p1[0] + ($p2[0] - $p1[0]) * ($minY - $p1[1]) / ($p2[1] - $p1[1]);
                $y = $minY;
            } elseif ($cOut & 2) { // Right
                $y = $p1[1] + ($p2[1] - $p1[1]) * ($maxX - $p1[0]) / ($p2[0] - $p1[0]);
                $x = $maxX;
            } elseif ($cOut & 1) { // Left
                $y = $p1[1] + ($p2[1] - $p1[1]) * ($minX - $p1[0]) / ($p2[0] - $p1[0]);
                $x = $minX;
            }

            if ($cOut === $c1) {
                $p1 = [$x, $y];
                $c1 = $code($p1);
            } else {
                $p2 = [$x, $y];
                $c2 = $code($p2);
            }
        }
    }

    /**
     * Performs Sutherland-Hodgman polygon clipping.
     *
     * @param  array  $polygon  The polygon coordinates.
     * @param  array  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return array The clipped polygon.
     */
    private static function sutherlandHodgmanClip(array $polygon, array $bbox): array
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;
        $clipEdges = [
            [$minX, $minY, $maxX, $minY], // Bottom
            [$maxX, $minY, $maxX, $maxY], // Right
            [$maxX, $maxY, $minX, $maxY], // Top
            [$minX, $maxY, $minX, $minY],  // Left
        ];

        foreach ($clipEdges as $edge) {
            if (empty($polygon)) {
                return [];
            }

            $newPolygon = [];
            $prevPoint = end($polygon);

            foreach ($polygon as $currPoint) {
                $insideCurr = self::insideBoundingBox($currPoint, $bbox);
                $insidePrev = self::insideBoundingBox($prevPoint, $bbox);

                if ($insideCurr) {
                    if (! $insidePrev) {
                        $newPolygon[] = self::lineIntersection($prevPoint, $currPoint, $bbox);
                    }
                    $newPolygon[] = $currPoint;
                } elseif ($insidePrev) {
                    $newPolygon[] = self::lineIntersection($prevPoint, $currPoint, $bbox);
                }

                $prevPoint = $currPoint;
            }

            $polygon = $newPolygon;
        }

        return $polygon;
    }

    /**
     * Checks if a point is inside the bounding box.
     *
     * @param  array  $point  The point [x, y].
     * @param  array  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return bool True if inside, false otherwise.
     */
    private static function insideBoundingBox(array $point, array $bbox): bool
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;

        return $point[0] >= $minX && $point[0] <= $maxX && $point[1] >= $minY && $point[1] <= $maxY;
    }

    /**
     * Finds the intersection of a line segment with a bounding box.
     *
     * @param  array  $p1  The first point [x, y].
     * @param  array  $p2  The second point [x, y].
     * @param  array  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return array The intersection point.
     */
    private static function lineIntersection(array $p1, array $p2, array $bbox): array
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;
        [$x1, $y1] = $p1;
        [$x2, $y2] = $p2;

        $intersect = function ($x, $y, $xMin, $xMax, $yMin, $yMax) {
            return max($xMin, min($x, $xMax)) === $x && max($yMin, min($y, $yMax)) === $y;
        };

        $candidates = [];

        if ($x1 !== $x2) {
            $t = ($minX - $x1) / ($x2 - $x1);
            if ($t >= 0 && $t <= 1) {
                $y = $y1 + $t * ($y2 - $y1);
                if ($intersect($minX, $y, $minX, $maxX, $minY, $maxY)) {
                    $candidates[] = [$minX, $y];
                }
            }
            $t = ($maxX - $x1) / ($x2 - $x1);
            if ($t >= 0 && $t <= 1) {
                $y = $y1 + $t * ($y2 - $y1);
                if ($intersect($maxX, $y, $minX, $maxX, $minY, $maxY)) {
                    $candidates[] = [$maxX, $y];
                }
            }
        }

        if ($y1 !== $y2) {
            $t = ($minY - $y1) / ($y2 - $y1);
            if ($t >= 0 && $t <= 1) {
                $x = $x1 + $t * ($x2 - $x1);
                if ($intersect($x, $minY, $minX, $maxX, $minY, $maxY)) {
                    $candidates[] = [$x, $minY];
                }
            }
        }

        return ! empty($candidates) ? $candidates[0] : $p1;
    }
}
