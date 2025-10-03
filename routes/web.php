 <?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HeritageController;
use App\Http\Controllers\DistrictPageController;
use App\Http\Controllers\MultiAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Vendor\VendorDashboardController;


// Smooth-scroll Home (single Blade page with sections)
Route::view('/', 'home')->name('home');

// Dedicated pages (so your nav buttons work)
Route::view('/shop',   'pages.shop')->name('shop');
Route::view('/cart',   'pages.cart')->name('cart');
Route::view('/skills', 'pages.skills')->name('skills');
Route::get('/heritage/{division}/{district?}', [HeritageController::class, 'page'])
     ->name('heritage.page');

     Route::get('/district/{slug}', [DistrictPageController::class, 'show'])->name('district.show');

     // forms post to these:
// routes/web.php
Route::prefix('auth')->group(function () {
    Route::post('/register', [\App\Http\Controllers\MultiAuthController::class, 'register'])->name('auth.register');
    Route::post('/login',    [\App\Http\Controllers\MultiAuthController::class, 'login'])->name('auth.login');
    Route::post('/logout',   [\App\Http\Controllers\MultiAuthController::class, 'logout'])->name('auth.logout');
});


// dashboards (replace views with yours later)
Route::middleware('auth')->get('/dashboard', fn() => view('dashboard'))->name('user.dashboard');
Route::middleware('auth:vendor')->get('/vendor/dashboard', fn() => view('vendor.dashboard'))->name('vendor.dashboard');
Route::middleware('auth:admin')->get('/admin/dashboard', fn() => view('admin.dashboard'))->name('admin.dashboard');

// USER: you already send users to route('home')
Route::get('/', fn() => view('home'))->name('home');

// VENDOR
Route::middleware(['auth:vendor'])->group(function () {
    Route::get('/vendor/dashboard', [VendorDashboardController::class, 'index'])
        ->name('vendor.dashboard');
});

// ADMIN
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});