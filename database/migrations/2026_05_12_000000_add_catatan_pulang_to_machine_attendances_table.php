<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machine_attendances', function (Blueprint $table) {
            $table->text('catatan_pulang')->nullable()->after('photo_pulang_at');
        });
    }

    public function down(): void
    {
        Schema::table('machine_attendances', function (Blueprint $table) {
            $table->dropColumn('catatan_pulang');
        });
    }
};
