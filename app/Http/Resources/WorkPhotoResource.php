<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkPhotoResource extends JsonResource
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
            'type' => $this->type,
            'photo_url' => $this->photo_url, // Menggunakan accessor
            'uploaded_by' => $this->uploader->name,
            'created_at' => $this->created_at,
        ];
    }
}
