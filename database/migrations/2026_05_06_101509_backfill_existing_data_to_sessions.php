<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfill existing data into the new sessions tables.
     * Order matters: invoice payment_status must be backfilled BEFORE SO status normalization,
     * because we need the original SO status values to determine payment_status.
     */
    public function up(): void
    {
        // Idempotency guard: if order_sessions already has rows, skip
        if (DB::table('order_sessions')->count() > 0) {
            return;
        }

        DB::transaction(function () {
            // Step A: Backfill invoices.payment_status from old SO statuses
            // (MUST run before Step D normalizes SO status)
            // invoices.service_order_id → service_orders.id

            DB::statement("
                UPDATE invoices SET payment_status = 'paid'
                WHERE service_order_id IN (
                    SELECT id FROM service_orders WHERE status = 'lunas'
                )
            ");

            DB::statement("
                UPDATE invoices SET payment_status = 'unpaid'
                WHERE service_order_id IN (
                    SELECT id FROM service_orders WHERE status IN ('invoiced', 'tagih', 'blm bayar')
                )
            ");

            DB::statement("
                UPDATE invoices SET payment_status = 'issued'
                WHERE payment_status = 'draft' AND service_order_id IN (
                    SELECT id FROM service_orders
                )
            ");

            // Step B: Create 1 order_session per existing service_order

            DB::statement("
                INSERT INTO order_sessions (service_order_id, session_number, tanggal, jam, type, status, notes, created_at, updated_at)
                SELECT
                    id,
                    1,
                    work_date,
                    work_time,
                    'kerja',
                    CASE
                        WHEN status IN ('booked', 'proses', 'done', 'cancel', 'cancelled') THEN
                            CASE WHEN status = 'cancelled' THEN 'cancel' ELSE status END
                        ELSE 'done'
                    END,
                    work_notes,
                    NOW(),
                    NOW()
                FROM service_orders
            ");

            // Step C: Copy staff from service_order_staff to order_session_staff

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

            // Step D: Normalize service_orders.status to operational-only values

            DB::statement("
                UPDATE service_orders
                SET status = 'done'
                WHERE status IN ('invoiced', 'tagih', 'blm bayar', 'lunas')
            ");

            DB::statement("
                UPDATE service_orders
                SET status = 'cancel'
                WHERE status = 'cancelled'
            ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * Note: we cannot reverse the SO status normalization or invoice backfill cleanly.
     * This is acceptable since the feature is not in production.
     */
    public function down(): void
    {
        DB::table('order_session_staff')->truncate();
        DB::table('order_sessions')->truncate();
    }
};
