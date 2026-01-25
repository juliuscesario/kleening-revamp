<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaults = [
            'bank_name' => 'BCA',
            'bank_account_no' => '5933068888',
            'bank_account_name' => 'PT. Kilau Elok Indonesia',
        ];

        foreach ($defaults as $key => $value) {
            // Only insert if key doesn't exist to avoid overwriting existing data
            if (!DB::table('app_settings')->where('key', $key)->exists()) {
                DB::table('app_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't necessarily want to delete these keys on rollback as they might contain user data now.
        // But for completeness, we could remove the keys we added.
        // For safety, we usually leave data migrations alone in down() unless specifically required.
    }
};
