<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Services\Auth\AuthenticationService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->status !== UserStatusEnum::ACTIVE) {
            $this->authenticationService->logout($request);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your account is not active. Please contact the administrator.',
                ]);
        }

        return $next($request);
    }
}
