<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only students who created the booking can make a payment
        if (!Auth::check() || Auth::user()->role !== 'student') {
            return false;
        }

        $booking = Booking::find($this->booking_id);

        return $booking && $booking->student_id === Auth::id() &&
               in_array($booking->status, ['pending', 'confirmed']);
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
            'payment_method' => 'required|string|in:credit_card,bank_transfer,wallet,paypal',
            'amount' => 'required|numeric|min:0',
            'payment_details' => 'nullable|array',
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
            'payment_method.required' => 'Please select a payment method',
            'payment_method.in' => 'The selected payment method is not valid',
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Payment amount cannot be negative',
        ];
    }
}
