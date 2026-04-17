<?php

namespace App\Enums;

enum StockAdjustmentTypeEnum: string
{
    case INCREASE = 'increase';
    case DECREASE = 'decrease';
}
