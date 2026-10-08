<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ManufacturingOrderController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PublicController;

// Public Routes
Route::get('/', [PublicController::class, 'index'])->name('public.home');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');
Route::get('/contact', fn() => view('public.contact'))->name('public.contact');
Route::post('/contact', [PublicController::class, 'contactSend'])->name('public.contact.send');
Route::get('/shop', [PublicController::class, 'shop'])->name('public.shop');
Route::get('/product/{product}', [PublicController::class, 'show'])->name('public.show');
Route::post('/product/{product}/review', [PublicController::class, 'submitReview'])->name('public.review.submit');

// Cart & Checkout
Route::get('/cart', [PublicController::class, 'cart'])->name('public.cart');
Route::post('/cart/add/{product}', [PublicController::class, 'addToCart'])->name('public.cart.add');
Route::delete('/cart/remove/{id}', [PublicController::class, 'removeFromCart'])->name('public.cart.remove');
Route::post('/cart/update/{id}', [PublicController::class, 'updateCart'])->name('public.cart.update');
Route::get('/checkout', [PublicController::class, 'checkout'])->name('public.checkout');
Route::post('/checkout', [PublicController::class, 'placeOrder'])->name('public.order.place');

// Public Auth Routes
Route::get('/client/login', [\App\Http\Controllers\ClientAuthController::class, 'showLogin'])->name('client.login');
Route::post('/client/login', [\App\Http\Controllers\ClientAuthController::class, 'login'])->name('client.login.submit');
Route::get('/client/register', [\App\Http\Controllers\ClientAuthController::class, 'showRegister'])->name('client.register');
Route::post('/client/register', [\App\Http\Controllers\ClientAuthController::class, 'register'])->name('client.register.submit');
Route::post('/client/logout', [\App\Http\Controllers\ClientAuthController::class, 'logout'])->name('client.logout');

// Client Dashboard
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\ClientDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [\App\Http\Controllers\ClientDashboardController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/rate/{product}', [\App\Http\Controllers\ClientDashboardController::class, 'submitRating'])->name('orders.rate');
    Route::get('/reviews', [\App\Http\Controllers\ClientDashboardController::class, 'reviews'])->name('reviews');
});

// Admin Auth routes (redirected to filament login)
Route::get('/login', fn() => redirect('/admin/login'))->name('login');

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory
    Route::resource('products', ProductController::class);
    Route::resource('stock-receipts', StockReceiptController::class);
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Sales
    Route::resource('sales', SaleController::class);
    Route::resource('sale-returns', SaleReturnController::class);
    Route::get('/invoice/{sale}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoice.show');

    // Production
    Route::resource('manufacturing-orders', ManufacturingOrderController::class);
    Route::resource('recipes', RecipeController::class);

    // Master Data
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitOfMeasureController::class);
    Route::resource('branches', BranchController::class);

    // Admin
    Route::resource('users', UserController::class);

    // Reports & Audit
    Route::get('/reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/production', [\App\Http\Controllers\ReportController::class, 'production'])->name('reports.production');
    Route::get('/audit', [\App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit.index');

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});
