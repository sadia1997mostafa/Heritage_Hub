<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\ReturnRequest;
use App\Policies\ReturnRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();

        // map policies
        \Illuminate\Support\Facades\Gate::policy(ReturnRequest::class, ReturnRequestPolicy::class);

        // Define vendor-only gate
        Gate::define('vendor-only', function (User $user) {
            return $user->isVendor();
        });

        // Define admin-only gate (simple role flag)
        Gate::define('admin-only', function (User $user) {
            return $user->isAdmin();
        });
    }
}
