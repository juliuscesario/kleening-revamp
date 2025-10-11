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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('dp_type')->nullable()->after('grand_total');
            $table->decimal('dp_value', 15, 2)->nullable()->after('dp_type');
            $table->decimal('total_after_dp', 15, 2)->nullable()->after('dp_value');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_after_dp');
            $table->decimal('balance', 15, 2)->storedAs('grand_total - paid_amount')->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['dp_type', 'dp_value', 'total_after_dp', 'paid_amount', 'balance']);
        });
    }
};