<?php

namespace App\Enums;

enum ReturnConditionEnum: string
{
    case GOOD = 'good';
    case DAMAGED = 'damaged';
    case NEEDS_REPAIR = 'needs_repair';
    case LOST = 'lost';
}
