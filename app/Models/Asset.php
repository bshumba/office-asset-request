<?php

namespace App\Models;

use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_category_id',
        'department_id',
        'name',
        'slug',
        'asset_code',
        'brand',
        'model',
        'serial_number',
        'description',
        'unit_type',
        'quantity_total',
        'quantity_available',
        'reorder_level',
        'track_serial',
        'status',
        'purchase_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'track_serial' => 'boolean',
            'purchase_date' => 'date',
            'status' => AssetStatusEnum::class,
            'unit_type' => AssetUnitTypeEnum::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assetRequests(): HasMany
    {
        return $this->hasMany(AssetRequest::class);
    }

    public function assetIssues(): HasMany
    {
        return $this->hasMany(AssetIssue::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(AssetAdjustment::class);
    }
}
