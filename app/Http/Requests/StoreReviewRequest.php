<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only students who completed the booking can leave a review
        if (!Auth::check() || Auth::user()->role !== 'student') {
            return false;
        }

        $booking = Booking::find($this->booking_id);

        // Check if booking exists, belongs to the student, and is completed
        return $booking &&
               $booking->student_id === Auth::id() &&
               $booking->status === 'completed';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_id' => 'required|exists:bookings,id',
            'mentor_id' => 'required|exists:users,id,role,mentor',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'rating_aspects' => 'nullable|array',
            'rating_aspects.*.aspect' => 'required|string',
            'rating_aspects.*.score' => 'required|integer|min:1|max:5',
            'is_anonymous' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'booking_id.required' => 'The booking ID is required',
            'booking_id.exists' => 'The selected booking does not exist',
            'mentor_id.required' => 'The mentor ID is required',
            'mentor_id.exists' => 'The selected mentor does not exist',
            'rating.required' => 'Please provide a rating',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating cannot be greater than 5',
        ];
    }
}
