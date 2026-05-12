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
        // 1. Fix for PostgreSQL where varchar(255) is too small for signatures
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE order_session_staff ALTER COLUMN signature_image TYPE TEXT');
        } else {
            Schema::table('order_session_staff', function (Blueprint $table) {
                $table->text('signature_image')->nullable()->change();
            });
        }

        // 2. Re-run backfill for staff if it was missed
        DB::statement("
            INSERT INTO order_session_staff (order_session_id, staff_id, signature_image, created_at, updated_at)
            SELECT
                os.id,
                sos.staff_id,
                sos.signature_image,
                NOW(),
                NOW()
            FROM service_order_staff sos
            JOIN order_sessions os ON os.service_order_id = sos.service_order_id
            WHERE NOT EXISTS (
                SELECT 1 FROM order_session_staff oss 
                WHERE oss.order_session_id = os.id AND oss.staff_id = sos.staff_id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE order_session_staff ALTER COLUMN signature_image TYPE VARCHAR(255)');
        } else {
            Schema::table('order_session_staff', function (Blueprint $table) {
                $table->string('signature_image', 255)->nullable()->change();
            });
        }
    }
};
