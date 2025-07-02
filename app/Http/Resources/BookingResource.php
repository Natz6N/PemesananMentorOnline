<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_code' => $this->booking_code,
            'student_id' => $this->student_id,
            'mentor_id' => $this->mentor_id,
            'mentor_profile_id' => $this->mentor_profile_id,
            'scheduled_at' => $this->scheduled_at,
            'duration_minutes' => $this->duration_minutes,
            'total_amount' => (float) $this->total_amount,
            'session_topic' => $this->session_topic,
            'student_notes' => $this->student_notes,
            'mentor_notes' => $this->mentor_notes,
            'status' => $this->status,
            'meeting_link' => $this->meeting_link,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student' => new UserResource($this->whenLoaded('student')),
            'mentor' => new UserResource($this->whenLoaded('mentor')),
            'mentor_profile' => new MentorProfileResource($this->whenLoaded('mentorProfile')),
        ];
    }
}
