<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "success" => true,
            "message" => "Berhasil mengambil data",
            "data" => [
                "id" => $this->id,
                "name" => $this->name,
                "slug" => $this->slug,
                "description" => $this->description,
                "icon" => $this->icon,
                "is_active" => $this->is_active ? true : false,
            ]
        ];
    }
}
