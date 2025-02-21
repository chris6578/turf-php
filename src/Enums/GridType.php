<?php

namespace willvincent\Turf\Enums;

enum GridType: string
{
    case POINT = 'point';
    case SQUARE = 'square';
    case HEX = 'hex';
    case TRIANGLE = 'triangle';
}
