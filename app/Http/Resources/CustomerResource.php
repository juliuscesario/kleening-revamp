<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'last_order_date' => $this->last_order_date, // <-- Menggunakan Accessor
            'created_at' => $this->created_at,
            // Kita bisa juga memuat alamat jika diminta
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
