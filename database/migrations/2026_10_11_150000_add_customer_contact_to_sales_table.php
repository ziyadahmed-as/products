<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add dedicated customer_email and customer_phone columns to the sales table
     * so we can store the order contact info separately from the combined
     * customer_name string, and link orders to accounts after registration.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->text('customer_address')->nullable()->after('customer_phone');
            $table->text('order_notes')->nullable()->after('customer_address');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone', 'customer_address', 'order_notes']);
        });
    }
};
