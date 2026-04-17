<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the signed-in user's settings page.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('profile.edit', [
            'user' => $user->load(['department', 'roles']),
        ]);
    }

    /**
     * Update profile details.
     */
    public function update(
        UpdateProfileRequest $request,
        ProfileService $profileService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $profileService->update($user, $request);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profile details updated successfully.');
    }

    /**
     * Update the signed-in user's password.
     */
    public function updatePassword(
        UpdatePasswordRequest $request,
        ProfileService $profileService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $profileService->updatePassword($user, $request);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Password updated successfully.');
    }
}
