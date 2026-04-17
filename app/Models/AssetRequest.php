<?php

namespace App\Models;

use App\Enums\AssetRequestStatusEnum;
use App\Enums\RequestPriorityEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'user_id',
        'department_id',
        'asset_id',
        'quantity_requested',
        'quantity_approved',
        'reason',
        'needed_by_date',
        'priority',
        'status',
        'manager_reviewed_by',
        'manager_reviewed_at',
        'manager_comment',
        'admin_reviewed_by',
        'admin_reviewed_at',
        'admin_comment',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'needed_by_date' => 'date',
            'manager_reviewed_at' => 'datetime',
            'admin_reviewed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'priority' => RequestPriorityEnum::class,
            'status' => AssetRequestStatusEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function managerReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_reviewed_by');
    }

    public function adminReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_reviewed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(AssetRequestStatusHistory::class);
    }

    public function issue(): HasOne
    {
        return $this->hasOne(AssetIssue::class);
    }
}
