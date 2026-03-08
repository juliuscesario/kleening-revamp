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
        $tables = [
            'users',
            'areas',
            'customers',
            'service_categories',
            'addresses',
            'services',
            'staff',
            'service_orders',
            'invoices',
            'payments',
            'expenses',
            'expense_categories',
            'notifications',
            'scheduler_logs',
            'app_settings',
            'service_order_items',
            'work_photos',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Add tenant_id column
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                
                // Special handling for unique constraints
                if ($tableName === 'users') {
                    $table->dropUnique(['phone_number']);
                    $table->unique(['tenant_id', 'phone_number']);
                }
                if ($tableName === 'app_settings') {
                    $table->dropUnique(['key']);
                    $table->unique(['tenant_id', 'key']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'areas',
            'customers',
            'service_categories',
            'addresses',
            'services',
            'staff',
            'service_orders',
            'invoices',
            'payments',
            'expenses',
            'expense_categories',
            'notifications',
            'scheduler_logs',
            'app_settings',
            'service_order_items',
            'work_photos',
        ];

        foreach (array_reverse($tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if ($tableName === 'users') {
                    $table->dropUnique(['tenant_id', 'phone_number']);
                    $table->unique(['phone_number']);
                }
                if ($tableName === 'app_settings') {
                    $table->dropUnique(['tenant_id', 'key']);
                    $table->unique(['key']);
                }
                
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
}
;
