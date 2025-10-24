 <?php

use Illuminate\Support\Facades\Route;
// Payment (mock) routes for M6 scaffolding
Route::get('/payments/mock/{id}', [App\Http\Controllers\PaymentController::class, 'mockRedirect'])->name('payment.mock.redirect');
Route::get('/payments/mock/{id}/return', [App\Http\Controllers\PaymentController::class, 'mockReturn'])->name('payment.mock.return');
Route::post('/payments/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');
Route::post('/payments/create', [App\Http\Controllers\PaymentController::class, 'create'])->name('payment.create');

// SSLCommerz sandbox integration (skeleton)
Route::get('/payments/sslcommerz/{intent}', [App\Http\Controllers\SslCommerzController::class, 'checkout'])->name('sslcommerz.checkout');
Route::post('/payments/sslcommerz/ipn', [App\Http\Controllers\SslCommerzController::class, 'ipn'])->name('sslcommerz.ipn');
// bKash sandbox scaffold
Route::get('/payments/bkash/{intent}', [App\Http\Controllers\BkashController::class, 'checkout'])->name('bkash.checkout');
Route::post('/payments/bkash/ipn', [App\Http\Controllers\BkashController::class, 'ipn'])->name('bkash.ipn');
Route::post('/payments/bkash/create', [App\Http\Controllers\BkashController::class, 'create'])->name('bkash.create');
Route::post('/payments/bkash/execute', [App\Http\Controllers\BkashController::class, 'execute'])->name('bkash.execute');
Route::get('/payments/bkash/{intent}/approve', [App\Http\Controllers\BkashController::class, 'approve'])->name('bkash.approve');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\HeritageController;
use App\Http\Controllers\DistrictPageController;
use App\Http\Controllers\MultiAuthController;
use App\Http\Controllers\VlogController;

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
// Returns
Route::middleware('auth')->group(function() {
    Route::get('/returns', [App\Http\Controllers\ReturnRequestController::class,'index'])->name('returns.index');
    Route::get('/returns/create', [App\Http\Controllers\ReturnRequestController::class,'create'])->name('returns.create');
    Route::post('/returns', [App\Http\Controllers\ReturnRequestController::class,'store'])->name('returns.store');
});

// Reviews (user)
Route::middleware('auth')->group(function() {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class,'store'])->name('reviews.store');
});
Route::middleware('auth')->group(function() {
    Route::get('/order/{order}/confirm', [\App\Http\Controllers\CartController::class,'confirm'])->name('cart.confirm');

    // Customer order pages
    Route::get('/orders', [\App\Http\Controllers\UserOrderController::class,'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\UserOrderController::class,'show'])->name('orders.show');
});
Route::view('/skills', 'pages.skills')->name('skills');

// Vlog timeline route must be registered before the generic heritage route so it isn't swallowed by the
// dynamic parameter route `/heritage/{division}`. Register it here, above the generic handler.
Route::get('/heritage/timeline', [VlogController::class,'index'])->name('vlogs.index');
Route::post('/heritage/timeline', [VlogController::class,'store'])->middleware('auth')->name('vlogs.store');
// Vlog management
Route::middleware('auth')->group(function () {
    Route::get('/heritage/timeline/{vlog}/edit', [VlogController::class,'edit'])->name('vlogs.edit');
    Route::post('/heritage/timeline/{vlog}', [VlogController::class,'update'])->name('vlogs.update');
    Route::delete('/heritage/timeline/{vlog}', [VlogController::class,'destroy'])->name('vlogs.destroy');
});

// Admin vlog moderation (under admin guard prefix)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/vlogs', [VlogController::class,'adminIndex'])->name('admin.vlogs.index');
    Route::post('/admin/vlogs/{vlog}/approve', [VlogController::class,'approve'])->name('admin.vlogs.approve');
});

// Admin events moderation
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/events', [\App\Http\Controllers\EventController::class,'adminIndex'])->name('admin.events.index');
    Route::post('/admin/events/{event}/approve', [\App\Http\Controllers\EventController::class,'approve'])->name('admin.events.approve');
});

// Events
use App\Http\Controllers\EventController;
Route::get('/events', [EventController::class,'index'])->name('events.index');
Route::post('/events', [EventController::class,'store'])->middleware('auth')->name('events.store');
Route::get('/events/{event}', [EventController::class,'show'])->name('events.show');
Route::post('/events/{event}/rsvp', [EventController::class,'toggleRsvp'])->middleware('auth')->name('events.rsvp');

Route::get('/heritage/{division}/{district?}', [HeritageController::class, 'page'])
    ->name('heritage.page');

Route::get('/district/{slug}', [DistrictPageController::class, 'show'])
    ->name('district.show');

// District chat proxy (server-side OpenAI) — accepts { question }
Route::post('/district/{slug}/chat', [\App\Http\Controllers\OpenAIChatController::class, 'chat'])
    ->name('district.chat');

// Public maker pages (reuses VendorProfile)
use App\Http\Controllers\MakerController;
Route::get('/makers/{slug}', [MakerController::class, 'show'])->name('makers.show');

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
    Route::delete('/vendor/store/gallery/{image}', [VendorOnboardingController::class,'removeGalleryImage'])->name('vendor.store.gallery.remove');
    Route::get('/vendor/store/attach-debug-form', [VendorOnboardingController::class,'attachDebugForm'])->name('vendor.store.attach_debug_form');

    Route::get('/vendor/payout',       [VendorOnboardingController::class,'payoutForm'])->name('vendor.payout.form');
    Route::post('/vendor/payout',      [VendorOnboardingController::class,'payoutSave'])->name('vendor.payout.save');
    Route::post('/vendor/store/attach-debug', [VendorOnboardingController::class,'attachDebugUploads'])->name('vendor.store.attach_debug');
    Route::get('/products', [\App\Http\Controllers\Vendor\ProductController::class,'index'])->name('vendor.products.index');
    Route::get('/products/create', [\App\Http\Controllers\Vendor\ProductController::class,'create'])->name('vendor.products.create');
    Route::post('/products', [\App\Http\Controllers\Vendor\ProductController::class,'store'])->name('vendor.products.store');
    Route::post('/products/{product}/submit', [\App\Http\Controllers\Vendor\ProductController::class,'submit'])->name('vendor.products.submit');
    Route::post('/products/{product}/adjust-stock', [\App\Http\Controllers\Vendor\ProductController::class,'adjustStock'])->name('vendor.products.adjust_stock');
    // Vendor orders
    Route::get('/vendor/orders', [\App\Http\Controllers\VendorOrderController::class,'index'])->name('vendor.orders.index');
    Route::get('/vendor/orders/{shipment}', [\App\Http\Controllers\VendorOrderController::class,'show'])->name('vendor.orders.show');
    Route::post('/vendor/orders/{shipment}', [\App\Http\Controllers\VendorOrderController::class,'updateStatus'])->name('vendor.orders.update');
    Route::get('/vendor/orders/{shipment}/packing-slip', [\App\Http\Controllers\VendorOrderController::class,'packingSlip'])->name('vendor.orders.packing-slip');
    Route::get('/vendor/ledger', [\App\Http\Controllers\VendorOrderController::class,'ledger'])->name('vendor.ledger');
    Route::post('/vendor/orders/{shipment}/quick-ship', [\App\Http\Controllers\VendorOrderController::class,'quickShip'])->name('vendor.orders.quickship');
    // Vendor return reviews
    Route::get('/vendor/returns', [\App\Http\Controllers\Vendor\ReturnRequestController::class,'index'])->name('vendor.returns.index');
    Route::get('/vendor/returns/{returnRequest}', [\App\Http\Controllers\Vendor\ReturnRequestController::class,'show'])->name('vendor.returns.show');
    Route::post('/vendor/returns/{returnRequest}/approve', [\App\Http\Controllers\Vendor\ReturnRequestController::class,'approve'])->name('vendor.returns.approve');
    Route::post('/vendor/returns/{returnRequest}/decline', [\App\Http\Controllers\Vendor\ReturnRequestController::class,'decline'])->name('vendor.returns.decline');
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

    // payout runs
    Route::get('/payout-runs', [\App\Http\Controllers\Admin\AdminPayoutRunController::class,'index'])->name('admin.payouts.runs.index');
    Route::post('/payout-runs/generate', [\App\Http\Controllers\Admin\AdminPayoutRunController::class,'generate'])->name('admin.payouts.runs.generate');
    Route::get('/payout-runs/{payout}/download', [\App\Http\Controllers\Admin\AdminPayoutRunController::class,'download'])->name('admin.payouts.runs.download');


    Route::prefix('admin')->group(function () {
        Route::get('/reports/platform-revenue', [\App\Http\Controllers\Admin\ReportController::class, 'platformRevenueProducts'])->name('admin.reports.platform-revenue');

        Route::get('/vendors',                   [AdminVendorController::class,'index'])->name('admin.vendors.index');
        Route::get('/vendors/{profile}',         [AdminVendorController::class,'show'])->name('admin.vendors.show');
        Route::post('/vendors/{profile}/approve',[AdminVendorController::class,'approve'])->name('admin.vendors.approve');
        Route::post('/vendors/{profile}/reject', [AdminVendorController::class,'reject'])->name('admin.vendors.reject');

    // Admin maker verify toggle
    Route::post('/makers/{profile}/verify', [\App\Http\Controllers\Admin\MakerController::class,'verify'])->name('admin.makers.verify');

        Route::get('/products', [\App\Http\Controllers\Admin\ProductApprovalController::class,'index'])->name('admin.products.index');
    Route::post('/products/{product}/approve', [\App\Http\Controllers\Admin\ProductApprovalController::class,'approve'])->name('admin.products.approve');
    Route::post('/products/{product}/reject', [\App\Http\Controllers\Admin\ProductApprovalController::class,'reject'])->name('admin.products.reject');
        // Returns review
        Route::get('/returns', [\App\Http\Controllers\Admin\ReturnRequestController::class,'index'])->name('admin.returns.index');
        Route::get('/returns/{returnRequest}', [\App\Http\Controllers\Admin\ReturnRequestController::class,'show'])->name('admin.returns.show');
        Route::post('/returns/{returnRequest}/approve', [\App\Http\Controllers\Admin\ReturnRequestController::class,'approve'])->name('admin.returns.approve');
        Route::post('/returns/{returnRequest}/decline', [\App\Http\Controllers\Admin\ReturnRequestController::class,'decline'])->name('admin.returns.decline');
        
    // Reviews moderation
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class,'index'])->name('admin.reviews.index');
    Route::post('/reviews/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class,'approve'])->name('admin.reviews.approve');
    Route::post('/reviews/{review}/hide', [\App\Http\Controllers\Admin\ReviewController::class,'hide'])->name('admin.reviews.hide');
        // Debug helpers
        Route::get('/debug/return-request/{returnRequest}', [\App\Http\Controllers\Admin\DebugController::class,'returnRequest'])->name('admin.debug.return-request');
    });
});



// Public shop browsing
Route::get('/c/{slug}', [CategoryController::class, 'show'])->name('shop.category.show');
Route::get('/p/{slug}', [ProductPublicController::class, 'show'])->name('shop.product.show');
Route::get('/store/{slug}', [StorefrontController::class, 'show'])->name('shop.store.show');



Route::get('/ping', fn () => 'pong');

// Heritage timeline routes are registered above the generic heritage route to avoid being matched
// by the dynamic `/heritage/{division}` route.

// Temporary debug upload endpoint (PUBLIC) - remove after debugging
use Illuminate\Http\Request as _Req;
Route::match(['get','post'], '/debug/vendor-upload', function(_Req $request) {
    if ($request->isMethod('post')) {
        $out = ['has_files'=>false,'files'=>[],'errors'=>[]];
        try {
            $out['has_files'] = count($request->files->all()) > 0;
            foreach ($request->files->all() as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $i => $f) {
                        // use putFileAs with a generated unique name to avoid calling hashName() on raw Symfony UploadedFile
                        $name = uniqid().'_'.preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $f->getClientOriginalName());
                        $path = Storage::disk('public')->putFileAs('debug/uploads', $f, $name);
                        $out['files'][] = ['key'=>$k,'index'=>$i,'clientName'=>$f->getClientOriginalName(),'path'=>$path];
                    }
                } else {
                    $f = $v;
                    $name = uniqid().'_'.preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $f->getClientOriginalName());
                    $path = Storage::disk('public')->putFileAs('debug/uploads', $f, $name);
                    $out['files'][] = ['key'=>$k,'clientName'=>$f->getClientOriginalName(),'path'=>$path];
                }
            }
        } catch (\Throwable $e) {
            $out['errors'][] = $e->getMessage();
        }
        return response()->json($out);
    }
    $token = csrf_token();
    $html = '<html><body>' .
            '<h3>Debug upload</h3>' .
            '<form method="POST" enctype="multipart/form-data">' .
            '<input type="file" name="gallery[]" multiple>' .
            '<button>Upload</button>' .
            '<input type="hidden" name="_token" value="' . $token . '">' .
            '</form></body></html>';
    return $html;
});

// Local notifications
Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark_all_read');

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
