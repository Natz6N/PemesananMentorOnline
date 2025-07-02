<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorProfileResource extends JsonResource
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
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'expertise' => $this->expertise,
            'experience_years' => $this->experience_years,
            'education' => $this->education,
            'current_position' => $this->current_position,
            'company' => $this->company,
            'achievements' => $this->achievements,
            'hourly_rate' => (float) $this->hourly_rate,
            'timezone' => $this->timezone,
            'languages' => $this->languages,
            'status' => $this->status,
            'rating_average' => (float) $this->rating_average,
            'total_reviews' => $this->total_reviews,
            'total_sessions' => $this->total_sessions,
            'is_available' => (bool) $this->is_available,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'availabilities' => MentorAvailabilityResource::collection($this->whenLoaded('availabilities')),
        ];
    }
}
