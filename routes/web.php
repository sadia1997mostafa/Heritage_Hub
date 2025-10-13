 <?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
// Cart & Checkout
Route::get('/cart', [\App\Http\Controllers\CartController::class,'index'])->name('cart');
Route::post('/cart/add', [\App\Http\Controllers\CartController::class,'add'])->name('cart.add');
Route::post('/cart/update', [\App\Http\Controllers\CartController::class,'update'])->name('cart.update');
Route::post('/cart/remove-vendor/{vendor}', [\App\Http\Controllers\CartController::class,'removeVendor'])->name('cart.remove.vendor');
Route::middleware('auth')->group(function() {
    Route::get('/checkout', [\App\Http\Controllers\CartController::class,'checkoutForm'])->name('checkout.form');
    Route::post('/checkout', [\App\Http\Controllers\CartController::class,'checkoutSubmit'])->name('checkout.submit');
});
Route::middleware('auth')->group(function() {
    Route::get('/order/{order}/confirm', [\App\Http\Controllers\CartController::class,'confirm'])->name('cart.confirm');

    // Customer order pages
    Route::get('/orders', [\App\Http\Controllers\UserOrderController::class,'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\UserOrderController::class,'show'])->name('orders.show');
});
Route::view('/skills', 'pages.skills')->name('skills');

Route::get('/heritage/{division}/{district?}', [HeritageController::class, 'page'])
    ->name('heritage.page');

Route::get('/district/{slug}', [DistrictPageController::class, 'show'])
    ->name('district.show');

// Login page (GET) — renders a real login form. Accepts optional ?redirect= URL to return after login.
Route::get('/login', function (Illuminate\Http\Request $request) {
    return view('auth.login', ['redirect' => $request->query('redirect')]);
})->name('login');

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
    // Vendor orders
    Route::get('/vendor/orders', [\App\Http\Controllers\VendorOrderController::class,'index'])->name('vendor.orders.index');
    Route::get('/vendor/orders/{shipment}', [\App\Http\Controllers\VendorOrderController::class,'show'])->name('vendor.orders.show');
    Route::post('/vendor/orders/{shipment}', [\App\Http\Controllers\VendorOrderController::class,'updateStatus'])->name('vendor.orders.update');
    Route::get('/vendor/orders/{shipment}/packing-slip', [\App\Http\Controllers\VendorOrderController::class,'packingSlip'])->name('vendor.orders.packing-slip');
    Route::get('/vendor/ledger', [\App\Http\Controllers\VendorOrderController::class,'ledger'])->name('vendor.ledger');
    Route::post('/vendor/orders/{shipment}/quick-ship', [\App\Http\Controllers\VendorOrderController::class,'quickShip'])->name('vendor.orders.quickship');
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

/*
|--------------------------------------------------------------------------
| Quick jump helper (site-facing)
| A tiny convenience to jump to known slugs from the UI while browsing.
| Example: /goto?type=product&slug=my-product
|--------------------------------------------------------------------------
*/
Route::get('/goto', function (Request $request) {
    $type = $request->query('type');
    $slug = $request->query('slug');
    if (!$slug) {
        abort(404);
    }
    switch ($type) {
        case 'category':
            return redirect()->route('shop.category.show', $slug);
        case 'product':
            return redirect()->route('shop.product.show', $slug);
        case 'store':
            return redirect()->route('shop.store.show', $slug);
        case 'district':
            return redirect()->route('district.show', $slug);
        default:
            return redirect()->route('shop');
    }
})->name('goto');
