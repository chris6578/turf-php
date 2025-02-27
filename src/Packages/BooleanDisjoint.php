<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanDisjoint
{
    public function __invoke(Geometry $feature1, Geometry $feature2): bool
    {
        // Case 1: Two Points
        if ($feature1 instanceof Point && $feature2 instanceof Point) {
            return ! Helpers::compareCoords(
                $feature1->getCoordinates(),
                $feature2->getCoordinates()
            );
        }

        // Case 2: Two LineStrings
        if ($feature1 instanceof LineString && $feature2 instanceof LineString) {
            return ! Turf::booleanIntersect($feature1, $feature2);
        }

        // Case 3: Two Polygons (existing implementation)
        if ($feature1 instanceof Polygon && $feature2 instanceof Polygon) {
            // Check if any vertex of feature1 is inside feature2
            foreach ($feature1->getCoordinates()[0] as $coord) {
                if (Turf::booleanPointInPolygon(new Point($coord), $feature2)) {
                    return false; // Overlap detected
                }
            }

            // Check if any vertex of feature2 is inside feature1
            foreach ($feature2->getCoordinates()[0] as $coord) {
                if (Turf::booleanPointInPolygon(new Point($coord), $feature1)) {
                    return false; // Overlap detected
                }
            }

            // Check edge intersections
            $edges1 = $this->getEdges($feature1);
            $edges2 = $this->getEdges($feature2);
            foreach ($edges1 as $edge1) {
                foreach ($edges2 as $edge2) {
                    if ($this->doEdgesIntersect($edge1[0], $edge1[1], $edge2[0], $edge2[1])) {
                        return false; // Intersection detected
                    }
                }
            }

            return true; // No overlap or intersection
        }

        throw new InvalidArgumentException('Unsupported geometry types');
    }

    /**
     * @param Polygon $polygon
     * @return mixed[]
     */
    private function getEdges(Polygon $polygon): array
    {
        $coords = $polygon->getCoordinates()[0];
        $edges = [];
        for ($i = 0; $i < count($coords) - 1; $i++) {
            $edges[] = [$coords[$i], $coords[$i + 1]];
        }
        return $edges;
    }

    /**
     * @param float[] $p1
     * @param float[] $p2
     * @param float[] $p3
     * @param float[] $p4
     * @return bool
     */
    private function doEdgesIntersect(array $p1, array $p2, array $p3, array $p4): bool
    {
        $o1 = $this->orientation($p1, $p2, $p3);
        $o2 = $this->orientation($p1, $p2, $p4);
        $o3 = $this->orientation($p3, $p4, $p1);
        $o4 = $this->orientation($p3, $p4, $p2);

        if ($o1 !== $o2 && $o3 !== $o4) {
            return true;
        }

        return false;
    }

    /**
     * @param float[] $p
     * @param float[] $q
     * @param float[] $r
     * @return int
     */
    private function orientation(array $p, array $q, array $r): int
    {
        $val = ($q[1] - $p[1]) * ($r[0] - $q[0]) - ($q[0] - $p[0]) * ($r[1] - $q[1]);
        if (abs($val) < 1e-10) return 0;
        return ($val > 0) ? 1 : 2;
    }
}
