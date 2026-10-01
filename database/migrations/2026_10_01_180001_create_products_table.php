<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('resale_product'); // raw_material, resale_product, manufactured_product
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_of_measure_id')->nullable()->constrained()->nullOnDelete();
            $table->text('specification')->nullable();
            $table->string('package_size')->nullable();
            $table->decimal('minimum_stock_level', 10, 2)->default(0);
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
