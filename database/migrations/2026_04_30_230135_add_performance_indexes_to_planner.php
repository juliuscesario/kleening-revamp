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
        // Index for daily planner queries (WHERE work_date = ?)
        Schema::table('service_orders', function (Blueprint $table) {
            $table->index('work_date');
        });

        // Index for staff-based lookups on the pivot table
        Schema::table('service_order_staff', function (Blueprint $table) {
            $table->index('staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropIndex(['work_date']);
        });

        Schema::table('service_order_staff', function (Blueprint $table) {
            $table->dropIndex(['staff_id']);
        });
    }
};
