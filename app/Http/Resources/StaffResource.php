<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource; // <-- Tambahkan Http di sini

class StaffResource extends JsonResource
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
            'is_active' => $this->is_active,
            'area' => [
                'id' => $this->area->id,
                'name' => $this->area->name,
            ],
            // Kita gunakan whenLoaded agar data user hanya muncul jika diminta
            'user_account' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
