<?php

namespace App\Models;

use App\Enums\ReturnConditionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_issue_id',
        'received_by_user_id',
        'quantity_returned',
        'condition_on_return',
        'remarks',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'condition_on_return' => ReturnConditionEnum::class,
        ];
    }

    public function assetIssue(): BelongsTo
    {
        return $this->belongsTo(AssetIssue::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
