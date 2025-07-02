<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'rating_aspects' => $this->rating_aspects,
            'is_anonymous' => $this->is_anonymous,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student' => $this->when(!$this->is_anonymous, function() {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->name,
                ];
            }),
            'mentor' => [
                'id' => $this->mentor->id,
                'name' => $this->mentor->name,
            ],
        ];
    }
}
