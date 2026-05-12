<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrderSession;
use App\Models\ServiceOrder;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Actions\CreateOrderSessionAction;
use App\Actions\UpdateOrderSessionAction;
use App\Actions\DeleteOrderSessionAction;

class OrderSessionController extends Controller
{
    /**
     * List all sessions for a service order.
     */
    public function list(ServiceOrder $serviceOrder)
    {
        $sessions = $serviceOrder->sessions()
            ->with('staff')
            ->get()
            ->map(fn($s) => $this->formatSessionResponse($s));

        return response()->json($sessions);
    }

    /**
     * Create a new session for a service order.
     */
    public function store(Request $request, ServiceOrder $serviceOrder)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'jam' => 'nullable|date_format:H:i',
            'type' => 'required|in:kerja,pickup,delivery,survey,workshop,rework',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'exists:staff,id',
            'notes' => 'nullable|string',
        ]);

        $action = new CreateOrderSessionAction();
        $session = $action->execute(
            $serviceOrder,
            $validated,
            $request->input('staff_ids', [])
        );

        return response()->json([
            'success' => true,
            'session' => $this->formatSessionResponse($session),
        ]);
    }

    /**
     * Update an existing session.
     */
    public function update(Request $request, OrderSession $orderSession)
    {
        $serviceOrder = $orderSession->serviceOrder;
        $serviceOrder->load('invoice');

        if ($serviceOrder->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak dapat diedit karena invoice sudah lunas.',
            ], 403);
        }

        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'jam' => 'nullable|date_format:H:i',
            'type' => 'nullable|in:kerja,pickup,delivery,survey,workshop,rework',
            'status' => 'nullable|in:booked,proses,done,cancel',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'exists:staff,id',
            'notes' => 'nullable|string',
        ]);

        $action = new UpdateOrderSessionAction();
        $session = $action->execute(
            $orderSession,
            $validated,
            $request->input('staff_ids')
        );

        return response()->json([
            'success' => true,
            'session' => $this->formatSessionResponse($session),
        ]);
    }

    /**
     * Delete a session (cannot delete the last one).
     */
    public function destroy(OrderSession $orderSession)
    {
        $serviceOrder = $orderSession->serviceOrder;
        $serviceOrder->load('invoice');

        if ($serviceOrder->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak dapat dihapus karena invoice sudah lunas.',
            ], 403);
        }

        try {
            $action = new DeleteOrderSessionAction();
            $action->execute($orderSession);

            return response()->json(['success' => true]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Format a session for JSON response.
     */
    protected function formatSessionResponse(OrderSession $session): array
    {
        $jamFormatted = null;
        if ($session->jam) {
            try {
                $jamFormatted = \Carbon\Carbon::parse($session->jam)->format('H:i');
            } catch (\Throwable $e) {
                $jamFormatted = $session->jam;
            }
        }

        return [
            'id' => $session->id,
            'session_number' => $session->session_number,
            'tanggal' => $session->tanggal ? $session->tanggal->format('Y-m-d') : null,
            'jam' => $jamFormatted,
            'type' => $session->type,
            'type_label' => $session->type_label,
            'status' => $session->status,
            'status_label' => $session->status_label,
            'notes' => $session->notes,
            'staff_ids' => $session->staff->pluck('id')->toArray(),
            'staff_names' => $session->staff->pluck('name')->toArray(),
        ];
    }
}
