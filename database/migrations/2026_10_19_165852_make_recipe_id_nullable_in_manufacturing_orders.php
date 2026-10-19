<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes recipe_id nullable so manufacturing orders can be created without
     * linking to an existing recipe (e.g. when using fully custom materials).
     */
    public function up(): void
    {
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            // Drop the old foreign key constraint first, then redefine as nullable
            $table->dropForeign(['recipe_id']);
            $table->foreignId('recipe_id')->nullable()->change()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->foreignId('recipe_id')->nullable(false)->change()->constrained()->cascadeOnDelete();
        });
    }
};
