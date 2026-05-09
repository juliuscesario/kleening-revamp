<?php

namespace App\Http\Controllers\Web;

use App\Models\ServiceOrder;
use App\Models\FinalOrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FinalOrderController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            'role:owner,admin,staff',
        ];
    }

    public function upsert(Request $request, ServiceOrder $serviceOrder)
    {
        $invoice = $serviceOrder->invoice;

        if ($invoice && $invoice->payment_status !== 'unpaid') {
            return response()->json([
                'success' => false,
                'message' => 'Final order sudah terkunci karena invoice telah diproses.',
            ], 403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        FinalOrderConfirmation::updateOrCreate(
            ['service_order_id' => $serviceOrder->id],
            [
                'content' => $request->content,
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Final order berhasil disimpan.',
        ]);
    }
}
