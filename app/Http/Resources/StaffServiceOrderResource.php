<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffServiceOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'so_number' => $this->so_number,
            'work_date' => $this->work_date,
            'status' => $this->status,
            'items' => ServiceOrderItemResource::collection($this->whenLoaded('items'))->map(function ($item) {
                return [
                    'id' => $item->id,
                    'service_id' => $item->service_id,
                    'service_name' => $item->service->name, // Assuming service is loaded
                    'quantity' => $item->quantity,
                ];
            }),
            'staff' => StaffResource::collection($this->whenLoaded('staff')),
            'work_notes' => $this->work_notes,
            'staff_notes' => $this->staff_notes,
            'created_at' => $this->created_at,
        ];
    }
}
