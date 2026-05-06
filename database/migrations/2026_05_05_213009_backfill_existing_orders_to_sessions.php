<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Idempotency guard: skip if order_sessions already has rows
        $existingCount = DB::table('order_sessions')->count();
        if ($existingCount > 0) {
            return;
        }

        // 1. Insert one session per existing service_order
        DB::statement("
            INSERT INTO order_sessions (service_order_id, session_number, tanggal, jam, type, status, notes, created_at, updated_at)
            SELECT
                id,
                1,
                work_date,
                work_time,
                'kerja',
                CASE
                    WHEN status IN ('booked', 'proses', 'done') THEN status
                    ELSE 'done'
                END,
                work_notes,
                NOW(),
                NOW()
            FROM service_orders
        ");

        // 2. Copy staff assignments from service_order_staff to order_session_staff
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
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('TRUNCATE order_session_staff');
        DB::statement('TRUNCATE order_sessions');
    }
};
