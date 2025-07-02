<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\MentorAvailabilitie;

class UpdateMentorAvailabilitieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya mentor (untuk profilenya sendiri) atau admin yang dapat mengupdate ketersediaan
        $availability = $this->route('mentorAvailabilitie');

        if (Auth::user()->role === 'admin') {
            return true;
        }

        if (Auth::user()->role === 'mentor') {
            $mentorProfile = $availability->mentorProfile;
            return $mentorProfile && $mentorProfile->user_id === Auth::id();
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_of_week' => 'sometimes|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
            'is_active' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'day_of_week.in' => 'Hari harus berupa: monday, tuesday, wednesday, thursday, friday, saturday, sunday',
            'start_time.date_format' => 'Format waktu mulai harus H:i:s',
            'end_time.date_format' => 'Format waktu selesai harus H:i:s',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak berwenang untuk mengupdate ketersediaan mentor ini.');
    }
}
