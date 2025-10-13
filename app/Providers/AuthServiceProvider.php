<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Define vendor-only gate
        Gate::define('vendor-only', function (User $user) {
            return isset($user->vendorProfile) && $user->vendorProfile->status === 'approved';
        });

        // Define admin-only gate (simple role flag)
        Gate::define('admin-only', function (User $user) {
            return $user->is_admin ?? false;
        });
    }
}
