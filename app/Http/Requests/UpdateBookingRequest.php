<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
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
            'scheduled_at' => 'sometimes|date|after:now',
            'duration_minutes' => 'sometimes|integer|min:15|max:180',
            'total_amount' => 'sometimes|numeric|min:0',
            'session_topic' => 'sometimes|string',
            'student_notes' => 'nullable|string',
            'mentor_notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled,no_show',
            'meeting_link' => 'nullable|url',
            'cancellation_reason' => 'nullable|string',
            'cancelled_at' => 'nullable|date',
        ];
    }
    public function messages()
    {
        return [
            'scheduled_at.date' => 'Tanggal dan waktu harus dalam format yang valid.',
            'scheduled_at.after' => 'Waktu sesi harus setelah waktu sekarang.',
            'duration_minutes.integer' => 'Durasi harus berupa angka (dalam menit).',
            'duration_minutes.min' => 'Durasi minimal adalah 15 menit.',
            'duration_minutes.max' => 'Durasi maksimal adalah 180 menit.',
            'total_amount.numeric' => 'Jumlah total harus berupa angka.',
            'total_amount.min' => 'Jumlah total tidak boleh kurang dari 0.',
            'session_topic.string' => 'Topik sesi harus berupa teks.',
            'student_notes.string' => 'Catatan siswa harus berupa teks.',
            'mentor_notes.string' => 'Catatan mentor harus berupa teks.',
            'status.in' => 'Status yang dipilih tidak valid. Pilihan yang tersedia: pending, confirmed, completed, cancelled, no_show.',
            'meeting_link.url' => 'Link pertemuan harus berupa URL yang valid.',
            'cancellation_reason.string' => 'Alasan pembatalan harus berupa teks.',
            'cancelled_at.date' => 'Tanggal pembatalan harus dalam format tanggal yang valid.',
        ];
    }

}
