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
        Schema::table('service_orders', function (Blueprint $table) {
            $table->longText('customer_signature_image')->nullable()->after('staff_notes');
        });

        Schema::table('service_order_staff', function (Blueprint $table) {
            $table->longText('signature_image')->nullable()->after('staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('customer_signature_image');
        });

        Schema::table('service_order_staff', function (Blueprint $table) {
            $table->dropColumn('signature_image');
        });
    }
};