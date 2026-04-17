<?php

namespace App\Services\Auth;

use App\Enums\UserStatusEnum;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthenticationService
{
    /**
     * Authenticate the incoming user.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): void
    {
        if (! Auth::attempt($request->credentials(), $request->shouldRemember())) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user instanceof User) {
            throw new RuntimeException('Authenticated user could not be resolved.');
        }

        if ($user->status !== UserStatusEnum::ACTIVE) {
            $this->logout($request);

            throw ValidationException::withMessages([
                'email' => 'Your account is not active. Please contact the administrator.',
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }

    /**
     * Log out the authenticated user and invalidate the session.
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
