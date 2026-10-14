<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Event Subscriber for Auth activity logging
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\AuthActivitySubscriber::class);

        // Model Observers for audit and activity tracking
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Sale::observe(\App\Observers\SaleObserver::class);
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\StockMovement::observe(\App\Observers\StockMovementObserver::class);
        \App\Models\StockReceipt::observe(\App\Observers\StockReceiptObserver::class);
        \App\Models\StockTransfer::observe(\App\Observers\StockTransferObserver::class);
        \App\Models\ManufacturingOrder::observe(\App\Observers\ManufacturingOrderObserver::class);
        \App\Models\ProductReview::observe(\App\Observers\ProductReviewObserver::class);
    }
}
