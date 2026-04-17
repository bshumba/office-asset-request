<?php

namespace App\Enums;

enum AssetIssueStatusEnum: string
{
    case ISSUED = 'issued';
    case PARTIALLY_RETURNED = 'partially_returned';
    case RETURNED = 'returned';
}
