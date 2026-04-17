<?php

namespace App\Models;

use App\Enums\AssetIssueStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_request_id',
        'asset_id',
        'issued_to_user_id',
        'issued_by_user_id',
        'department_id',
        'quantity_issued',
        'issued_at',
        'expected_return_date',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expected_return_date' => 'date',
            'status' => AssetIssueStatusEnum::class,
        ];
    }

    public function assetRequest(): BelongsTo
    {
        return $this->belongsTo(AssetRequest::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function issuedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to_user_id');
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(AssetReturn::class);
    }

    public function returnedQuantity(): int
    {
        return (int) $this->returns()->sum('quantity_returned');
    }

    public function outstandingQuantity(): int
    {
        return max(0, $this->quantity_issued - $this->returnedQuantity());
    }
}
