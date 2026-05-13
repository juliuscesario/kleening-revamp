<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('hadirr_nik', 50)->nullable()->after('phone_number');
            $table->index('hadirr_nik');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropIndex(['hadirr_nik']);
            $table->dropColumn('hadirr_nik');
        });
    }
};
