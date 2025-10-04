 <?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HeritageController;
use App\Http\Controllers\DistrictPageController;
use App\Http\Controllers\MultiAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\VendorOnboardingController;
use App\Http\Controllers\AdminVendorController;


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




Route::middleware(['auth'])->group(function () {
    Route::get('/vendor/apply',  [VendorOnboardingController::class,'applyForm'])->name('vendor.apply.form');
    Route::post('/vendor/apply', [VendorOnboardingController::class,'applySubmit'])->name('vendor.apply.submit');

    Route::middleware('vendor.approved')->group(function () {
        Route::get('/vendor/store/setup', [VendorOnboardingController::class,'setupForm'])->name('vendor.store.setup');
        Route::post('/vendor/store/setup', [VendorOnboardingController::class,'setupSave'])->name('vendor.store.setup.save');
        Route::get('/vendor/payout', [VendorOnboardingController::class,'payoutForm'])->name('vendor.payout.form');
        Route::post('/vendor/payout', [VendorOnboardingController::class,'payoutSave'])->name('vendor.payout.save');
    });
});

// admin area
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/vendors', [AdminVendorController::class,'index'])->name('admin.vendors.index');
    Route::get('/vendors/{profile}', [AdminVendorController::class,'show'])->name('admin.vendors.show');
    Route::post('/vendors/{profile}/approve', [AdminVendorController::class,'approve'])->name('admin.vendors.approve');
    Route::post('/vendors/{profile}/reject', [AdminVendorController::class,'reject'])->name('admin.vendors.reject');
});