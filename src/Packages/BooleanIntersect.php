<?php

declare(strict_types=1);

namespace willvincent\Turf\Packages;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Polygon;
use InvalidArgumentException;
use willvincent\Turf\Turf;

class BooleanIntersect
{
    public function __invoke(Geometry $geometry1, Geometry $geometry2): bool
    {
        if (get_class($geometry1) == LineString::class && get_class($geometry2) == LineString::class) {
            return $this->doLinesIntersect($geometry1, $geometry2);
        }

        return !Turf::booleanDisjoint($geometry1, $geometry2);
    }

    private function doLinesIntersect(LineString $line1, LineString $line2): bool
    {
        $coords1 = $line1->getCoordinates();
        $coords2 = $line2->getCoordinates();
        for ($i = 0; $i < count($coords1) - 1; $i++) {
            for ($j = 0; $j < count($coords2) - 1; $j++) {
                if ($this->doEdgesIntersect(
                    $coords1[$i],
                    $coords1[$i + 1],
                    $coords2[$j],
                    $coords2[$j + 1]
                )) {
                    return true;
                }
            }
        }
        return false;
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
