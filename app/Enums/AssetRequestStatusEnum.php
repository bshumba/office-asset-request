<?php

namespace App\Enums;

enum AssetRequestStatusEnum: string
{
    case PENDING = 'pending';
    case MANAGER_APPROVED = 'manager_approved';
    case ADMIN_APPROVED = 'admin_approved';
    case REJECTED = 'rejected';
    case ISSUED = 'issued';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
}
