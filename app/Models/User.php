<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatusEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'department_id',
    'name',
    'email',
    'password',
    'status',
    'last_login_at',
    'notes',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Department Manager');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('Staff');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function managedDepartment(): HasOne
    {
        return $this->hasOne(Department::class, 'manager_user_id');
    }

    public function assetRequests(): HasMany
    {
        return $this->hasMany(AssetRequest::class);
    }

    public function managerReviewedRequests(): HasMany
    {
        return $this->hasMany(AssetRequest::class, 'manager_reviewed_by');
    }

    public function adminReviewedRequests(): HasMany
    {
        return $this->hasMany(AssetRequest::class, 'admin_reviewed_by');
    }

    public function cancelledRequests(): HasMany
    {
        return $this->hasMany(AssetRequest::class, 'cancelled_by');
    }

    public function issuedAssets(): HasMany
    {
        return $this->hasMany(AssetIssue::class, 'issued_to_user_id');
    }

    public function issueActions(): HasMany
    {
        return $this->hasMany(AssetIssue::class, 'issued_by_user_id');
    }

    public function receivedReturns(): HasMany
    {
        return $this->hasMany(AssetReturn::class, 'received_by_user_id');
    }

    public function createdAdjustments(): HasMany
    {
        return $this->hasMany(AssetAdjustment::class, 'created_by');
    }

    public function requestStatusHistories(): HasMany
    {
        return $this->hasMany(AssetRequestStatusHistory::class, 'acted_by');
    }

    public function notificationsLog(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
