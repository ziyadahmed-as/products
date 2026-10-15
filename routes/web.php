<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ManufacturingOrderController;
use App\Http\Controllers\ManufacturingDashboardController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PublicController;

// ── Public Store Routes ────────────────────────────────────────────────────
Route::get('/',          [PublicController::class, 'index'])->name('public.home');
Route::get('/about',     [PublicController::class, 'about'])->name('public.about');
Route::get('/contact',   fn() => view('public.contact'))->name('public.contact');
Route::post('/contact',  [PublicController::class, 'contactSend'])->name('public.contact.send');
Route::get('/shop',      [PublicController::class, 'shop'])->name('public.shop');
Route::get('/product/{product}',          [PublicController::class, 'show'])->name('public.show');
Route::post('/product/{product}/review',  [PublicController::class, 'submitReview'])->name('public.review.submit');

// Cart & Checkout
Route::get('/cart',                 [PublicController::class, 'cart'])->name('public.cart');
Route::post('/cart/add/{product}',  [PublicController::class, 'addToCart'])->name('public.cart.add');
Route::delete('/cart/remove/{id}',  [PublicController::class, 'removeFromCart'])->name('public.cart.remove');
Route::post('/cart/update/{id}',    [PublicController::class, 'updateCart'])->name('public.cart.update');
Route::get('/checkout',             [PublicController::class, 'checkout'])->name('public.checkout');
Route::post('/checkout',            [PublicController::class, 'placeOrder'])->name('public.order.place');

// Order Success / Post-order account prompt
Route::get('/order/success', [PublicController::class, 'orderSuccess'])->name('public.order.success');

// ── Unified Authentication Routes (All Users: Super Admin, Manager, Seller, Client) ──
Route::middleware('guest')->group(function () {
    Route::get('/login',           [\App\Http\Controllers\ClientAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',          [\App\Http\Controllers\ClientAuthController::class, 'login'])->name('login.submit');
    Route::get('/client/login',    fn() => redirect()->route('login'))->name('client.login');
    Route::post('/client/login',   [\App\Http\Controllers\ClientAuthController::class, 'login'])->name('client.login.submit');
    Route::get('/admin/login',     fn() => redirect()->route('login'))->name('filament.admin.auth.login');
    Route::get('/client/register', [\App\Http\Controllers\ClientAuthController::class, 'showRegister'])->name('client.register');
    Route::post('/client/register',[\App\Http\Controllers\ClientAuthController::class, 'register'])->name('client.register.submit');

    // Password Reset
    Route::get('/client/forgot-password',         [\App\Http\Controllers\ClientAuthController::class, 'showForgotPassword'])->name('client.password.request');
    Route::post('/client/forgot-password',        [\App\Http\Controllers\ClientAuthController::class, 'sendResetLink'])->name('client.password.email');
    Route::get('/client/reset-password/{token}',  [\App\Http\Controllers\ClientAuthController::class, 'showResetPassword'])->name('client.password.reset');
    Route::post('/client/reset-password',         [\App\Http\Controllers\ClientAuthController::class, 'resetPassword'])->name('client.password.update');
});

Route::post('/client/logout', [\App\Http\Controllers\ClientAuthController::class, 'logout'])->name('client.logout');

// ── Client Dashboard (auth-protected) ─────────────────────────────────────
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard',                      [\App\Http\Controllers\ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders',                         [\App\Http\Controllers\ClientDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}',                 [\App\Http\Controllers\ClientDashboardController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/rate/{product}', [\App\Http\Controllers\ClientDashboardController::class, 'submitRating'])->name('orders.rate');
    Route::get('/reviews',                        [\App\Http\Controllers\ClientDashboardController::class, 'reviews'])->name('reviews');
    Route::get('/reports',                        [\App\Http\Controllers\ClientDashboardController::class, 'reports'])->name('reports');
    Route::get('/reports/export',                 [\App\Http\Controllers\ClientDashboardController::class, 'exportOrders'])->name('reports.export');
    Route::get('/profile',                        [\App\Http\Controllers\ClientDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile',                        [\App\Http\Controllers\ClientDashboardController::class, 'updateProfile'])->name('profile.update');
});

// ── Admin / Internal Routes (auth required, non-client) ───────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory
    Route::resource('products',      ProductController::class);
    Route::resource('stock-receipts', StockReceiptController::class);
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Sales
    Route::resource('sales',        SaleController::class);
    Route::resource('sale-returns', SaleReturnController::class);
    Route::get('/invoice/{sale}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoice.show');

    // Production & Manufacturing
    Route::get('/manufacturing/dashboard', [ManufacturingDashboardController::class, 'index'])->name('manufacturing.dashboard');
    Route::post('/manufacturing-orders/{order}/submit-approval', [ManufacturingOrderController::class, 'submitApproval'])->name('manufacturing-orders.submit-approval');
    Route::post('/manufacturing-orders/{order}/approve', [ManufacturingOrderController::class, 'approve'])->name('manufacturing-orders.approve');
    Route::post('/manufacturing-orders/{order}/issue-materials', [ManufacturingOrderController::class, 'issueMaterials'])->name('manufacturing-orders.issue-materials');
    Route::post('/manufacturing-orders/{order}/start-production', [ManufacturingOrderController::class, 'startProduction'])->name('manufacturing-orders.start-production');
    Route::post('/manufacturing-orders/{order}/complete', [ManufacturingOrderController::class, 'complete'])->name('manufacturing-orders.complete');
    Route::post('/manufacturing-orders/{order}/cancel', [ManufacturingOrderController::class, 'cancel'])->name('manufacturing-orders.cancel');
    Route::resource('manufacturing-orders', ManufacturingOrderController::class);
    Route::resource('recipes',              RecipeController::class);

    // Master Data
    Route::resource('categories', CategoryController::class);
    Route::resource('units',      UnitOfMeasureController::class);
    Route::resource('branches',   BranchController::class);

    // Admin
    Route::resource('users', UserController::class);

    // Reports & Analytics Suite
    Route::get('/reports',                     [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales',               [\App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/sales/export',        [\App\Http\Controllers\ReportController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('/reports/inventory',           [\App\Http\Controllers\ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/inventory/export',    [\App\Http\Controllers\ReportController::class, 'exportInventory'])->name('reports.inventory.export');
    Route::get('/reports/production',          [\App\Http\Controllers\ReportController::class, 'production'])->name('reports.production');
    Route::get('/reports/production/export',   [\App\Http\Controllers\ReportController::class, 'exportProduction'])->name('reports.production.export');
    Route::get('/reports/branches',            [\App\Http\Controllers\ReportController::class, 'branchPerformance'])->name('reports.branches');
    Route::get('/reports/branches/export',     [\App\Http\Controllers\ReportController::class, 'exportBranchPerformance'])->name('reports.branches.export');
    Route::get('/reports/transfers',           [\App\Http\Controllers\ReportController::class, 'transfers'])->name('reports.transfers');
    Route::get('/reports/transfers/export',    [\App\Http\Controllers\ReportController::class, 'exportTransfers'])->name('reports.transfers.export');
    Route::get('/reports/activity',            [\App\Http\Controllers\AuditTrailController::class, 'index'])->name('reports.activity');
    Route::get('/reports/activity/export',     [\App\Http\Controllers\AuditTrailController::class, 'export'])->name('reports.activity.export');
    Route::get('/reports/user-performance/export', [\App\Http\Controllers\ReportController::class, 'exportUserPerformance'])->name('reports.users.export');
    Route::get('/audit',                       [\App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit.index');

    // ── POS API Helpers (AJAX) ─────────────────────────────────────────────
    Route::get('/api/products/search', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $q    = $request->input('q', '');
        $branchId = $request->input('branch_id');

        $query = \App\Models\Product::with(['unit', 'category'])
            ->where('is_active', true)
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%");
            })
            ->limit(10);

        return response()->json($query->get()->map(function ($p) {
            return [
                'id'            => $p->id,
                'name'          => $p->name,
                'sku'           => $p->sku,
                'selling_price' => $p->selling_price ?? 0,
                'quantity'      => $p->quantity ?? 0,
                'unit_code'     => $p->unit?->code ?? $p->unit?->name ?? 'unit',
            ];
        }));
    })->name('api.products.search');

    Route::get('/api/branches/{branch}/locations', function (\App\Models\Branch $branch) {
        $locations = \App\Models\StorageLocation::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($locations);
    })->name('api.branches.locations');

    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});

