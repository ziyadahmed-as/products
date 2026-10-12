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
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('planned_date');
            $table->date('expected_completion_date')->nullable()->after('start_date');
            $table->dateTime('actual_start_date')->nullable()->after('expected_completion_date');
            $table->dateTime('actual_completion_date')->nullable()->after('actual_start_date');
            $table->foreignId('unit_of_measure_id')->nullable()->after('storage_location_id')->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('production_notes')->nullable()->after('total_cost');
            $table->text('wastage_reason')->nullable()->after('production_notes');
            $table->boolean('is_materials_issued')->default(false)->after('status');
            $table->boolean('is_finished_registered')->default(false)->after('is_materials_issued');
        });

        Schema::table('material_issues', function (Blueprint $table) {
            $table->decimal('quantity_planned', 15, 2)->default(0)->after('product_id');
            $table->decimal('quantity_returned', 15, 2)->default(0)->after('quantity_consumed');
            $table->foreignId('unit_of_measure_id')->nullable()->after('quantity_returned')->constrained()->nullOnDelete();
            $table->decimal('unit_cost', 15, 2)->default(0)->after('unit_of_measure_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_issues', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropColumn([
                'quantity_planned',
                'quantity_returned',
                'unit_of_measure_id',
                'unit_cost',
            ]);
        });

        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn([
                'start_date',
                'expected_completion_date',
                'actual_start_date',
                'actual_completion_date',
                'unit_of_measure_id',
                'assigned_user_id',
                'production_notes',
                'wastage_reason',
                'is_materials_issued',
                'is_finished_registered',
            ]);
        });
    }
};
