<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\MentorProfile;

class StoreMentorAvailabilitieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya mentor (untuk profilenya sendiri) atau admin yang dapat membuat ketersediaan
        if (Auth::user()->role === 'admin') {
            return true;
        }

        if (Auth::user()->role === 'mentor') {
            $mentorProfile = MentorProfile::find($this->mentor_profile_id);
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
            'mentor_profile_id' => 'required|exists:mentor_profiles,id',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'mentor_profile_id.required' => 'ID profil mentor diperlukan',
            'mentor_profile_id.exists' => 'Profil mentor tidak ditemukan',
            'day_of_week.required' => 'Hari diperlukan',
            'day_of_week.in' => 'Hari harus berupa: monday, tuesday, wednesday, thursday, friday, saturday, sunday',
            'start_time.required' => 'Waktu mulai diperlukan',
            'start_time.date_format' => 'Format waktu mulai harus H:i:s',
            'end_time.required' => 'Waktu selesai diperlukan',
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
        throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak berwenang untuk membuat ketersediaan mentor ini.');
    }
}
