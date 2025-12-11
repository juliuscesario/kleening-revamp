<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ServiceOrderItemResource;

class ServiceOrderResource extends JsonResource
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
            'work_time' => $this->work_time,
            'work_time_formatted' => $this->work_time_formatted,
            'status' => $this->status,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'items' => ServiceOrderItemResource::collection($this->whenLoaded('items')),
            'staff' => StaffResource::collection($this->whenLoaded('staff')),
            'work_notes' => $this->work_notes,
            'staff_notes' => $this->staff_notes,
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at,
        ];
    }
}
