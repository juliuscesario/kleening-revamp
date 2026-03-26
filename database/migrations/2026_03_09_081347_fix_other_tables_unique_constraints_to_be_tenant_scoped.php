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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['phone_number']);
            $table->unique(['tenant_id', 'phone_number']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['tenant_id', 'name']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropUnique(['so_number']);
            $table->unique(['tenant_id', 'so_number']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->unique(['tenant_id', 'invoice_number']);
        });

        Schema::table('expense_categories', function (Blueprint $table) {
            $table->unique(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'name']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'invoice_number']);
            $table->unique(['invoice_number']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'so_number']);
            $table->unique(['so_number']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'name']);
            $table->unique(['name']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'phone_number']);
            $table->unique(['phone_number']);
        });
    }
};
