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
        Schema::table('service_categories', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->default(10.00)->after('name');
            // Stored as percentage: 10.00 = 10%, 30.00 = 30%
        });

        // Update existing rows with default commission rates
        DB::table('service_categories')->where('name', 'General Cleaning')->update(['commission_rate' => 30.00]);
        DB::table('service_categories')->where('name', 'Deep Cleaning')->update(['commission_rate' => 30.00]);
        DB::table('service_categories')->where('name', 'Poles')->update(['commission_rate' => 15.00]);
        DB::table('service_categories')->where('name', 'Poles Lantai')->update(['commission_rate' => 15.00]);
        // Everything else stays at default 10.00
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
