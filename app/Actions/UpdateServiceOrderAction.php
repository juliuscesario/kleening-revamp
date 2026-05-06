<?php

namespace App\Actions;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateServiceOrderAction
{
    public function execute(ServiceOrder $serviceOrder, array $soData, ?array $staffIds = null): ServiceOrder
    {
        return DB::transaction(function () use ($serviceOrder, $soData, $staffIds) {
            // Build update payload — only include fields that are present
            $updateData = [];

            if (array_key_exists('work_notes', $soData)) {
                $updateData['work_notes'] = $soData['work_notes'];
            }
            if (array_key_exists('staff_notes', $soData)) {
                $updateData['staff_notes'] = $soData['staff_notes'];
            }
            if (array_key_exists('status', $soData)) {
                $updateData['status'] = $soData['status'];
            }
            if (array_key_exists('work_date', $soData)) {
                $updateData['work_date'] = $soData['work_date'];
            }
            if (array_key_exists('work_time', $soData)) {
                $workTime = $soData['work_time'];
                // Convert H:i to H:i:s if needed
                if (is_string($workTime) && strlen($workTime) === 5) {
                    $workTime = $workTime . ':00';
                }
                $updateData['work_time'] = $workTime;
            }

            // Set work_proof_completed_at when status changes to done
            if (isset($updateData['status']) && $updateData['status'] === 'done') {
                $updateData['work_proof_completed_at'] = now();
            }

            if (!empty($updateData)) {
                $serviceOrder->update($updateData);
            }

            // Sync services if provided
            if (isset($soData['services']) && is_array($soData['services'])) {
                $currentServiceItemIds = $serviceOrder->items->pluck('id')->toArray();
                $newServiceItemIds = [];

                foreach ($soData['services'] as $serviceData) {
                    $service = Service::withoutGlobalScopes()->find($serviceData['service_id']);
                    $quantity = $serviceData['quantity'];
                    $price = $service->price;
                    $total = $price * $quantity;

                    if (isset($serviceData['id']) && in_array($serviceData['id'], $currentServiceItemIds)) {
                        // Update existing item
                        $item = ServiceOrderItem::find($serviceData['id']);
                        if ($item) {
                            $item->update([
                                'service_id' => $service->id,
                                'quantity'   => $quantity,
                                'price'      => $price,
                                'total'      => $total,
                            ]);
                        }
                        $newServiceItemIds[] = $serviceData['id'];
                    } else {
                        // Create new item
                        $newItem = ServiceOrderItem::create([
                            'service_order_id' => $serviceOrder->id,
                            'service_id'       => $service->id,
                            'quantity'         => $quantity,
                            'price'            => $price,
                            'total'            => $total,
                        ]);
                        $newServiceItemIds[] = $newItem->id;
                    }
                }

                // Delete removed service items
                ServiceOrderItem::where('service_order_id', $serviceOrder->id)
                    ->whereNotIn('id', $newServiceItemIds)
                    ->delete();
            }

            // For single-session orders, also update Session 1
            if (!$serviceOrder->is_multi_session) {
                $session = $serviceOrder->sessions()->first();
                if ($session) {
                    $sessionUpdate = [];
                    if (array_key_exists('work_date', $soData)) {
                        $sessionUpdate['tanggal'] = $soData['work_date'];
                    }
                    if (array_key_exists('work_time', $soData)) {
                        $workTime = $soData['work_time'];
                        if (is_string($workTime) && strlen($workTime) === 5) {
                            $workTime = $workTime . ':00';
                        }
                        $sessionUpdate['jam'] = $workTime;
                    }
                    if (array_key_exists('work_notes', $soData)) {
                        $sessionUpdate['notes'] = $soData['work_notes'];
                    }
                    if (!empty($sessionUpdate)) {
                        $session->update($sessionUpdate);
                    }

                    // Sync staff to Session 1
                    if ($staffIds !== null) {
                        $session->staff()->sync($staffIds);
                    }
                }
            }

            // Update customer's last_order_date if work_date changed
            if (array_key_exists('work_date', $soData) || isset($soData['services'])) {
                $serviceOrder->load('customer');
                if ($serviceOrder->customer) {
                    $serviceOrder->customer->syncLastOrderDate();
                }
            }

            return $serviceOrder->fresh();
        });
    }
}
