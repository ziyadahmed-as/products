<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds branch_id to sales table for direct branch-level tracking and security.
     * Backfills existing sales from their storage_location's branch.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Nullable so existing rows are not broken; we backfill below.
            $table->foreignId('branch_id')
                ->nullable()
                ->after('storage_location_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Backfill existing sales: derive branch_id from storage_location.branch_id
        \DB::statement('
            UPDATE sales
            SET branch_id = (
                SELECT branch_id FROM storage_locations
                WHERE storage_locations.id = sales.storage_location_id
            )
            WHERE branch_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
