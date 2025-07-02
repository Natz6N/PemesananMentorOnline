<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_code' => 'required|string|unique:bookings,booking_code',
            'student_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id',
            'mentor_profile_id' => 'required|exists:mentor_profiles,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'total_amount' => 'required|numeric|min:0',
            'session_topic' => 'required|string',
            'student_notes' => 'nullable|string',
            'meeting_link' => 'nullable|url',
        ];
    }
}
