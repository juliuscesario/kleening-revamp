<?php

namespace App\Actions;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\OrderSession;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class CreateServiceOrderAction
{
    public function execute(array $soData, array $staffIds = [], ?int $createdBy = null): ServiceOrder
    {
        return DB::transaction(function () use ($soData, $staffIds, $createdBy) {
            $soData['status'] = $soData['status'] ?? 'booked';

            // Generate SO number if not provided
            if (!isset($soData['so_number'])) {
                $soData['so_number'] = 'SO-' . date('Ymd') . '-' . str_pad(ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            if ($createdBy !== null) {
                $soData['created_by'] = $createdBy;
            }

            // Convert work_time to H:i:s if provided in H:i format
            if (isset($soData['work_time']) && strlen($soData['work_time']) === 5) {
                $soData['work_time'] = $soData['work_time'] . ':00';
            }

            $serviceOrder = ServiceOrder::create($soData);

            // Create Session 1
            $sessionStatus = $serviceOrder->status === 'cancel' ? 'cancel' : 'booked';

            $serviceOrder->sessions()->create([
                'session_number' => 1,
                'tanggal'        => $serviceOrder->work_date,
                'jam'            => $serviceOrder->work_time,
                'type'           => 'kerja',
                'status'         => $sessionStatus,
                'notes'          => $serviceOrder->work_notes,
            ]);

            // Assign staff to Session 1 (via order_session_staff)
            if (!empty($staffIds)) {
                $serviceOrder->sessions()->first()->staff()->attach($staffIds);
            }

            // Create service order items if provided
            if (isset($soData['services']) && is_array($soData['services'])) {
                $serviceIds = array_column($soData['services'], 'service_id');
                $services = Service::withoutGlobalScopes()->whereIn('id', $serviceIds)->get()->keyBy('id');

                foreach ($soData['services'] as $serviceData) {
                    $service = $services->get($serviceData['service_id']);
                    if ($service) {
                        $quantity = $serviceData['quantity'];
                        $price = $service->price;

                        ServiceOrderItem::create([
                            'service_order_id' => $serviceOrder->id,
                            'service_id'       => $service->id,
                            'price'            => $price,
                            'quantity'         => $quantity,
                            'total'            => $price * $quantity,
                        ]);
                    }
                }
            }

            // Update customer's last_order_date
            $serviceOrder->load('customer');
            if ($serviceOrder->customer) {
                $serviceOrder->customer->syncLastOrderDate();
            }

            return $serviceOrder;
        });
    }
}
