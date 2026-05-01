<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('last_order_date')->nullable()->after('phone_number');
        });

        // Populate from existing service_orders (max work_date per customer)
        DB::statement("
            UPDATE customers c
            SET last_order_date = (
                SELECT MAX(so.work_date)
                FROM service_orders so
                WHERE so.customer_id = c.id
                  AND so.status NOT IN ('cancelled')
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('last_order_date');
        });
    }
};
