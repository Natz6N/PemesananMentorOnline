<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\MentorProfile;

class UpdateMentorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the user is a mentor and owns this profile
        $profile = $this->route('mentor');

        return Auth::check() &&
               Auth::user()->role === 'mentor' &&
               $profile &&
               $profile->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => 'sometimes|required|string|min:100|max:2000',
            'expertise' => 'sometimes|required|array|min:1',
            'expertise.*' => 'string|max:50',
            'experience_years' => 'sometimes|required|integer|min:0|max:100',
            'education' => 'sometimes|required|string|max:500',
            'current_position' => 'sometimes|required|string|max:100',
            'company' => 'sometimes|required|string|max:100',
            'achievements' => 'nullable|string|max:1000',
            'hourly_rate' => 'sometimes|required|numeric|min:0',
            'timezone' => 'sometimes|required|string|max:50',
            'languages' => 'sometimes|required|array|min:1',
            'languages.*' => 'string|max:30',
            'is_available' => 'boolean',
            'category_ids' => 'sometimes|required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
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
            'bio.required' => 'Please provide your professional bio',
            'bio.min' => 'Your bio should be at least 100 characters',
            'expertise.required' => 'Please select at least one area of expertise',
            'experience_years.required' => 'Please specify your years of experience',
            'education.required' => 'Please provide your educational background',
            'hourly_rate.required' => 'Please set your hourly rate',
            'hourly_rate.min' => 'Hourly rate cannot be negative',
            'timezone.required' => 'Please specify your timezone',
            'languages.required' => 'Please specify at least one language you speak',
            'category_ids.required' => 'Please select at least one category',
            'category_ids.*.exists' => 'One or more selected categories do not exist',
        ];
    }
}
