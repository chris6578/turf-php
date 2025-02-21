<?php

namespace willvincent\Turf\Enums;

enum Corner: string
{
    case SW = 'sw';
    case SE = 'se';
    case NW = 'nw';
    case NE = 'ne';
    case CENTER = 'center';
    case CENTROID = 'centroid';
}
