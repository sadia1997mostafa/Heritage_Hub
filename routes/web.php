<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HeritageController;
use App\Http\Controllers\DistrictPageController;


// Smooth-scroll Home (single Blade page with sections)
Route::view('/', 'home')->name('home');

// Dedicated pages (so your nav buttons work)
Route::view('/shop',   'pages.shop')->name('shop');
Route::view('/cart',   'pages.cart')->name('cart');
Route::view('/skills', 'pages.skills')->name('skills');
Route::get('/heritage/{division}/{district?}', [HeritageController::class, 'page'])
     ->name('heritage.page');

     Route::get('/district/{slug}', [DistrictPageController::class, 'show'])->name('district.show');

// Keep Breeze/Fortify auth if you installed it
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
