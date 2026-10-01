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

// Redirect root to login or dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Auth routes
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

    // Production
    Route::resource('manufacturing-orders', ManufacturingOrderController::class);
    Route::resource('recipes', RecipeController::class);

    // Master Data
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitOfMeasureController::class);
    Route::resource('branches', BranchController::class);

    // Admin
    Route::resource('users', UserController::class);

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});

// Login / Register (simple redirect to Filament admin for now)
Route::get('/login', fn() => redirect('/admin/login'))->name('login');
