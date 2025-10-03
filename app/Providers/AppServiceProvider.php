<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\District;

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
        // Prefetch assets for performance
        Vite::prefetch(concurrency: 3);

        // Share districts with auth modal and home view
        View::composer([
            'components.auth-modal', // replace with actual path of your modal blade
            'home',                  // optional: if you also need districts on home
        ], function ($view) {
            $districts = District::query()
                ->orderBy('name')
                ->get(['id','name','slug']);

            $view->with('districts', $districts);
        });
    }
}
