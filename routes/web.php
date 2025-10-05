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
use App\Http\Controllers\Admin\AdminPayoutController;



use App\Http\Controllers\Shop\CategoryController;
use App\Http\Controllers\Shop\ProductPublicController;
use App\Http\Controllers\Shop\StorefrontController;


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
    Route::get('/products', [\App\Http\Controllers\Vendor\ProductController::class,'index'])->name('vendor.products.index');
    Route::get('/products/create', [\App\Http\Controllers\Vendor\ProductController::class,'create'])->name('vendor.products.create');
    Route::post('/products', [\App\Http\Controllers\Vendor\ProductController::class,'store'])->name('vendor.products.store');
    Route::post('/products/{product}/submit', [\App\Http\Controllers\Vendor\ProductController::class,'submit'])->name('vendor.products.submit');
});

/*
|--------------------------------------------------------------------------
| Admin (admin guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
   Route::get('/payouts',                [AdminPayoutController::class,'index'])->name('admin.payouts.index');
Route::post('/payouts/{payout}/ok',   [AdminPayoutController::class,'approve'])->name('admin.payouts.approve');
Route::post('/payouts/{payout}/nope', [AdminPayoutController::class,'reject'])->name('admin.payouts.reject');


    Route::prefix('admin')->group(function () {
        Route::get('/vendors',                   [AdminVendorController::class,'index'])->name('admin.vendors.index');
        Route::get('/vendors/{profile}',         [AdminVendorController::class,'show'])->name('admin.vendors.show');
        Route::post('/vendors/{profile}/approve',[AdminVendorController::class,'approve'])->name('admin.vendors.approve');
        Route::post('/vendors/{profile}/reject', [AdminVendorController::class,'reject'])->name('admin.vendors.reject');

        Route::get('/products', [\App\Http\Controllers\Admin\ProductApprovalController::class,'index'])->name('admin.products.index');
    Route::post('/products/{product}/approve', [\App\Http\Controllers\Admin\ProductApprovalController::class,'approve'])->name('admin.products.approve');
    Route::post('/products/{product}/reject', [\App\Http\Controllers\Admin\ProductApprovalController::class,'reject'])->name('admin.products.reject');
    });
});



// Public shop browsing
Route::get('/c/{slug}', [CategoryController::class, 'show'])->name('shop.category.show');
Route::get('/p/{slug}', [ProductPublicController::class, 'show'])->name('shop.product.show');
Route::get('/store/{slug}', [StorefrontController::class, 'show'])->name('shop.store.show');



Route::get('/ping', fn () => 'pong');
