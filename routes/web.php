 <?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HeritageController;
use App\Http\Controllers\DistrictPageController;
use App\Http\Controllers\MultiAuthController;

// Admin & Vendor controllers in their proper namespaces
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AdminVendorController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\VendorOnboardingController;

/*
|--------------------------------------------------------------------------
| Public / Site Routes
|--------------------------------------------------------------------------
*/
Route::view('/', 'home')->name('home');
Route::view('/shop',   'pages.shop')->name('shop');
Route::view('/cart',   'pages.cart')->name('cart');
Route::view('/skills', 'pages.skills')->name('skills');

Route::get('/heritage/{division}/{district?}', [HeritageController::class, 'page'])
    ->name('heritage.page');

Route::get('/district/{slug}', [DistrictPageController::class, 'show'])
    ->name('district.show');

// Minimal login alias so guard redirects have a target
Route::get('/login', fn () => redirect()->route('home')->with('please_login', true))
    ->name('login');

/*
|--------------------------------------------------------------------------
| Auth (multi-auth forms)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [MultiAuthController::class, 'register'])->name('auth.register');
    Route::post('/login',    [MultiAuthController::class, 'login'])->name('auth.login');
    Route::post('/logout',   [MultiAuthController::class, 'logout'])->name('auth.logout');
});

/*
|--------------------------------------------------------------------------
| User dashboard (web guard)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/dashboard', fn () => view('dashboard'))->name('user.dashboard');

/*
|--------------------------------------------------------------------------
| Vendor – application (web guard)
| Any logged-in user can apply to become a vendor.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/vendor/apply',  [VendorOnboardingController::class,'applyForm'])->name('vendor.apply.form');
    Route::post('/vendor/apply', [VendorOnboardingController::class,'applySubmit'])->name('vendor.apply.submit');
});

/*
|--------------------------------------------------------------------------
| Vendor – panel (vendor guard + approval)
| DO NOT nest auth:web here. Vendor signs in via vendor guard.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:vendor', 'vendor.approved'])->group(function () {
    Route::get('/vendor/dashboard',    [VendorDashboardController::class,'index'])->name('vendor.dashboard');

    Route::get('/vendor/store/setup',  [VendorOnboardingController::class,'setupForm'])->name('vendor.store.setup');
    Route::post('/vendor/store/setup', [VendorOnboardingController::class,'setupSave'])->name('vendor.store.setup.save');

    Route::get('/vendor/payout',       [VendorOnboardingController::class,'payoutForm'])->name('vendor.payout.form');
    Route::post('/vendor/payout',      [VendorOnboardingController::class,'payoutSave'])->name('vendor.payout.save');
});

/*
|--------------------------------------------------------------------------
| Admin (admin guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('admin')->group(function () {
        Route::get('/vendors',                   [AdminVendorController::class,'index'])->name('admin.vendors.index');
        Route::get('/vendors/{profile}',         [AdminVendorController::class,'show'])->name('admin.vendors.show');
        Route::post('/vendors/{profile}/approve',[AdminVendorController::class,'approve'])->name('admin.vendors.approve');
        Route::post('/vendors/{profile}/reject', [AdminVendorController::class,'reject'])->name('admin.vendors.reject');
    });
});
