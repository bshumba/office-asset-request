<?php

namespace App\Providers;

use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\User;
use App\Policies\AssetIssuePolicy;
use App\Policies\AssetRequestPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AssetRequest::class, AssetRequestPolicy::class);
        Gate::policy(AssetIssue::class, AssetIssuePolicy::class);

        Gate::before(static function (User $user): ?bool {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
