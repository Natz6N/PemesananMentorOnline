<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the student who created the review can update it
        $review = $this->route('review');

        return Auth::check() &&
               Auth::user()->role === 'student' &&
               Auth::id() === $review->student_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => 'sometimes|required|integer|min:1|max:5',
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
            'rating.required' => 'Please provide a rating',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating cannot be greater than 5',
        ];
    }
}
