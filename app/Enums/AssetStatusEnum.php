<?php

namespace App\Enums;

enum AssetStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case RETIRED = 'retired';
}
