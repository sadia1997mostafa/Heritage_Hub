<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Cache;
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
            // Cache the districts list for a few hours to avoid a DB hit on every request
            $districts = Cache::remember('site:districts', now()->addHours(6), function () {
                return District::query()
                    ->orderBy('name')
                    ->get(['id','name','slug']);
            });

            $view->with('districts', $districts);
        });

        // Share featured content with the shop page so Home remains focused on hero/map
        View::composer(['pages.shop'], function ($view) {
            $cats = Cache::remember('site:featured_categories', now()->addHours(6), function () {
                return \App\Models\Category::query()->limit(8)->get();
            });

            $products = Cache::remember('site:featured_products', now()->addHours(2), function () {
                return \App\Models\Product::query()->approved()->inStock()->latest()->limit(12)->with('media')->get();
            });

            $vendors = Cache::remember('site:featured_vendors', now()->addHours(6), function () {
                // Prefer modern VendorProfile records but also include legacy Vendor rows
                $vp = collect();
                $legacy = collect();
                try {
                    if (class_exists('\App\\Models\\VendorProfile')) {
                        $vp = \App\Models\VendorProfile::query()->limit(8)->get();
                    }
                } catch (\Throwable $__e) {
                    $vp = collect();
                }

                try {
                    if (class_exists('\App\\Models\\Vendor')) {
                        $legacy = \App\Models\Vendor::query()->limit(8)->get();
                    }
                } catch (\Throwable $__e) {
                    $legacy = collect();
                }

                // Merge while preserving order: VendorProfile first, then legacy vendors.
                $merged = $vp->concat($legacy)->slice(0, 8)->values();
                return $merged;
            });

            $view->with('featuredCategories', $cats)
                 ->with('featuredProducts', $products)
                 ->with('featuredVendors', $vendors);
        });
    }
}
