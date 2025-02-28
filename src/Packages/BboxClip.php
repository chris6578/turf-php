<?php

namespace Turf\Packages;

use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;

class BboxClip
{
    /**
     * @param  float[]  $bbox
     */
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
     * @param  mixed[]  $line  The line coordinates.
     * @param  float[]  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return mixed[] The clipped line coordinates.
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
                if (empty($clipped)) {
                    $clipped = $clippedSegment;
                } else {
                    // Avoid duplicate points
                    if ($clipped[count($clipped) - 1] !== $clippedSegment[0]) {
                        $clipped[] = $clippedSegment[0];
                        $clipped[] = $clippedSegment[1];
                    } else {
                        $clipped[] = $clippedSegment[1];
                    }
                }
            }
        }

        return $clipped;
    }

    /**
     * Clips a Polygon using the Sutherland-Hodgman algorithm.
     *
     * @param  mixed[]  $rings  The polygon rings.
     * @param  float[]  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return mixed[] The clipped polygon rings.
     */
    private static function clipPolygon(array $rings, array $bbox): array
    {
        $clippedRings = [];
        foreach ($rings as $ring) {
            $clipped = self::sutherlandHodgmanClip($ring, $bbox);
            if (count($clipped) >= 3) { // At least 3 points for a valid polygon
                // Ensure the ring is closed
                if ($clipped[0] !== end($clipped)) {
                    $clipped[] = $clipped[0];
                }
                // Reverse to ensure counterclockwise orientation (if needed)
                $clippedRings[] = array_reverse($clipped);
            }
        }

        return $clippedRings;
    }

    /**
     * Performs Cohen-Sutherland line clipping algorithm.
     *
     * @param  float[]  $p1  First point [x, y].
     * @param  float[]  $p2  Second point [x, y].
     * @param  float[]  $bbox  Bounding box [minX, minY, maxX, maxY].
     * @return mixed[] Clipped line segment or null if outside.
     */
    private static function cohenSutherlandClip(array $p1, array $p2, array $bbox): array
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
     * @param  mixed[]  $polygon  The polygon coordinates.
     * @param  float[]  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return mixed[] The clipped polygon.
     */
    private static function sutherlandHodgmanClip(array $polygon, array $bbox): array
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;

        // Define clip edges in order: left, right, bottom, top
        $clipEdges = [
            'left' => [$minX, $minY, $minX, $maxY], // x = minX
            'right' => [$maxX, $minY, $maxX, $maxY], // x = maxX
            'bottom' => [$minX, $minY, $maxX, $minY], // y = minY
            'top' => [$minX, $maxY, $maxX, $maxY], // y = maxY
        ];

        $clippedPolygon = $polygon;

        foreach ($clipEdges as $edgeName => $edge) {
            if (empty($clippedPolygon)) {
                return [];
            }

            $newPolygon = [];
            $prevPoint = end($clippedPolygon); // Start with the last point
            reset($clippedPolygon);

            foreach ($clippedPolygon as $currPoint) {
                $insidePrev = self::inside($prevPoint, $edgeName, $bbox);
                $insideCurr = self::inside($currPoint, $edgeName, $bbox);

                if ($insideCurr) {
                    if (! $insidePrev) {
                        // Segment enters the clipping area; add intersection
                        $intersection = self::computeIntersection($prevPoint, $currPoint, $edgeName, $bbox);
                        if ($intersection && (empty($newPolygon) || $newPolygon[count($newPolygon) - 1] !== $intersection)) {
                            $newPolygon[] = $intersection;
                        }
                    }
                    // Add current point if not a duplicate
                    if (empty($newPolygon) || $newPolygon[count($newPolygon) - 1] !== $currPoint) {
                        $newPolygon[] = $currPoint;
                    }
                } elseif ($insidePrev) {
                    // Segment exits the clipping area; add intersection
                    $intersection = self::computeIntersection($prevPoint, $currPoint, $edgeName, $bbox);
                    if ($intersection && (empty($newPolygon) || $newPolygon[count($newPolygon) - 1] !== $intersection)) {
                        $newPolygon[] = $intersection;
                    }
                }

                $prevPoint = $currPoint;
            }

            $clippedPolygon = $newPolygon;
        }

        return $clippedPolygon;
    }

    /**
     * Checks if a point is inside relative to the clip edge.
     *
     * @param  float[]  $point  The point [x, y].
     * @param  string  $edgeName  The name of the clip edge ('left', 'right', 'bottom', 'top').
     * @param  float[]  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return bool True if inside, false otherwise.
     */
    private static function inside(array $point, string $edgeName, array $bbox): bool
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;
        switch ($edgeName) {
            case 'left':
                return $point[0] >= $minX;
            case 'right':
                return $point[0] <= $maxX;
            case 'bottom':
                return $point[1] >= $minY;
            case 'top':
                return $point[1] <= $maxY;
            default:
                return false;
        }
    }

    /**
     * Computes the intersection point between a line segment and a clip edge.
     *
     * @param  float[]  $s  Start point of the segment [x, y].
     * @param  float[]  $e  End point of the segment [x, y].
     * @param  string  $edgeName  The name of the clip edge ('left', 'right', 'bottom', 'top').
     * @param  float[]  $bbox  The bounding box [minX, minY, maxX, maxY].
     * @return float[]|null The intersection point [x, y] or null if no intersection.
     */
    private static function computeIntersection(array $s, array $e, string $edgeName, array $bbox): ?array
    {
        [$minX, $minY, $maxX, $maxY] = $bbox;
        $dx = $e[0] - $s[0];
        $dy = $e[1] - $s[1];

        switch ($edgeName) {
            case 'left':
                if ($dx == 0) {
                    return null;
                }
                $t = ($minX - $s[0]) / $dx;
                if ($t < 0 || $t > 1) {
                    return null;
                }
                $y = $s[1] + $t * $dy;

                return [$minX, $y];
            case 'right':
                if ($dx == 0) {
                    return null;
                }
                $t = ($maxX - $s[0]) / $dx;
                if ($t < 0 || $t > 1) {
                    return null;
                }
                $y = $s[1] + $t * $dy;

                return [$maxX, $y];
            case 'bottom':
                if ($dy == 0) {
                    return null;
                }
                $t = ($minY - $s[1]) / $dy;
                if ($t < 0 || $t > 1) {
                    return null;
                }
                $x = $s[0] + $t * $dx;

                return [$x, $minY];
            case 'top':
                if ($dy == 0) {
                    return null;
                }
                $t = ($maxY - $s[1]) / $dy;
                if ($t < 0 || $t > 1) {
                    return null;
                }
                $x = $s[0] + $t * $dx;

                return [$x, $maxY];
        }

        return null;
    }
}
