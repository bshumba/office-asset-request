<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    public const RESOURCE_ASSET_REQUEST = 'asset_request';
    public const RESOURCE_ASSET_ISSUE = 'asset_issue';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'resource_type',
        'resource_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
