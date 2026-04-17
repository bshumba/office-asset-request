<?php

namespace App\Models;

use App\Enums\StockAdjustmentReasonEnum;
use App\Enums\StockAdjustmentTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'type',
        'quantity',
        'reason',
        'reference',
        'note',
        'created_by',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    protected function casts(): array
    {
        return [
            'type' => StockAdjustmentTypeEnum::class,
            'reason' => StockAdjustmentReasonEnum::class,
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
