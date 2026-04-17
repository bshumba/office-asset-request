<?php

namespace App\Enums;

enum StockAdjustmentReasonEnum: string
{
    case INITIAL_STOCK = 'initial_stock';
    case RESTOCK = 'restock';
    case DAMAGE = 'damage';
    case LOSS = 'loss';
    case CORRECTION = 'correction';
}
