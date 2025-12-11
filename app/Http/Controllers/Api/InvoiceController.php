<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ServiceOrder;
use App\Models\Invoice;
use App\Http\Resources\ServiceOrderResource; // <-- Tambahkan Http di sini
use App\Http\Resources\InvoiceResource; // <-- Tambahkan Http di sini
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- 1. Tambahkan ini

class InvoiceController extends Controller
{
    use AuthorizesRequests; // <-- 2. Tambahkan ini
    /**
     * Membuat Invoice baru dari ServiceOrder yang ada.
     */
    public function storeFromServiceOrder(Request $request, ServiceOrder $serviceOrder)
    {
        
        // Cek izin: apakah user boleh melihat daftar Invoice?
        $this->authorize('create', Invoice::class);
        
        // 1. Cek apakah SO ini sudah punya invoice
        if ($serviceOrder->invoice()->exists()) {
            return response()->json(['message' => 'Service Order already has an invoice.'], 409); // 409 Conflict
        }

        // 2. Validasi input tambahan (misal: transport fee)
        $validated = $request->validate([
            'transport_fee' => 'sometimes|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($serviceOrder, $validated) {
            // 3. Kalkulasi total dari service order items
            $subtotal = $serviceOrder->items()->sum('total');
            $transportFee = $validated['transport_fee'] ?? 50000; // Default transport fee
            $grandTotal = $subtotal + $transportFee;

            // 4. Buat Invoice baru
            $newInvoice = $serviceOrder->invoice()->create([
                'invoice_number' => 'INV/' . Carbon::now()->format('Y/m/') . $serviceOrder->id,
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(7),
                'subtotal' => $subtotal,
                'transport_fee' => $transportFee,
                'grand_total' => $grandTotal,
                'status' => 'unpaid', // Status awal invoice
            ]);

            // 5. Update status Service Order menjadi 'invoiced'
            $serviceOrder->update(['status' => 'invoiced']);

            return $newInvoice;
        });

        return new InvoiceResource($invoice->load('serviceOrder'));
    }

    // --- Method CRUD Standar ---

    public function index()
    {
        // Cek izin: apakah user boleh melihat daftar Invoice?
        $this->authorize('viewAny', Invoice::class);

        return InvoiceResource::collection(Invoice::with('serviceOrder.customer')->get());
    }

    public function show(Invoice $invoice)
    {
        // Cek izin: apakah user boleh melihat daftar Invoice?
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load('serviceOrder'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Cek izin: apakah user boleh melihat daftar Invoice?
        $this->authorize('update', $invoice);

        // Method ini biasanya untuk update status pembayaran atau tanda tangan
        $validated = $request->validate([
            'status' => 'sometimes|string|in:unpaid,paid,overdue',
            'signature' => 'nullable|string',
        ]);

        $invoice->update($validated);
        return new InvoiceResource($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        // Cek izin: apakah user boleh melihat daftar Invoice?
        $this->authorize('delete', $invoice);
        // Hati-hati dengan logic ini, mungkin perlu revert status SO
        DB::transaction(function () use ($invoice) {
            $invoice->serviceOrder()->update(['status' => 'confirmed']);
            $invoice->delete();
        });

        return response()->noContent();
    }
}
