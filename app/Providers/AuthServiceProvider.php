<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\ReturnRequest;
use App\Policies\ReturnRequestPolicy;
use App\Models\Vlog;
use App\Policies\VlogPolicy;
use App\Models\Event;
// use App\Policies\EventPolicy; // optional: add if you implement event-edit permissions

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
    \Illuminate\Support\Facades\Gate::policy(Vlog::class, VlogPolicy::class);
    \Illuminate\Support\Facades\Gate::policy(Event::class, \App\Policies\EventPolicy::class);

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
