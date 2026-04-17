<?php

namespace App\Services\Profile;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;

class ProfileService
{
    /**
     * Update the signed-in user's profile details.
     */
    public function update(User $user, UpdateProfileRequest $request): User
    {
        $user->forceFill($request->profileData())->save();

        return $user->fresh(['department', 'roles']) ?? $user;
    }

    /**
     * Update the signed-in user's password.
     */
    public function updatePassword(User $user, UpdatePasswordRequest $request): void
    {
        $user->forceFill([
            'password' => $request->validated('password'),
        ])->save();
    }
}
