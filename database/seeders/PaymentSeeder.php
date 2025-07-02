<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all bookings that don't have payments yet
        $bookings = Booking::whereDoesntHave('payment')->get();

        foreach ($bookings as $booking) {
            // Determine payment status based on booking status
            $paymentStatus = 'pending';

            if ($booking->status === 'completed') {
                $paymentStatus = 'paid';
            } elseif ($booking->status === 'confirmed') {
                $paymentStatus = fake()->randomElement(['pending', 'paid', 'paid', 'paid']); // 75% chance to be paid
            } elseif ($booking->status === 'cancelled') {
                $paymentStatus = fake()->randomElement(['failed', 'refunded']);
            }

            $paymentCode = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));
            $paymentMethods = ['credit_card', 'bank_transfer', 'e-wallet', 'paypal'];
            $paymentMethod = fake()->randomElement($paymentMethods);

            $paymentDetails = null;
            $paidAt = null;
            $externalId = null;

            if ($paymentStatus === 'paid') {
                // For paid bookings, payment date is before the scheduled date
                // Fix: Ensure start date is always before end date
                $maxDate = min([now(), $booking->scheduled_at]);
                $minDate = now()->subDays(30); // Start from 30 days ago

                // If booking scheduled_at is too far in the past, use it as min date
                if ($booking->scheduled_at < $minDate) {
                    $minDate = $booking->scheduled_at->copy()->subDays(5);
                }

                $paidAt = fake()->dateTimeBetween($minDate, $maxDate);
                $externalId = strtoupper(Str::random(16));
                $paymentDetails = [
                    'transaction_id' => $externalId,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paidAt->format('Y-m-d H:i:s'),
                    'payment_status' => 'success',
                    'payment_processor' => 'gateway',
                ];
            } elseif ($paymentStatus === 'failed') {
                $paymentDetails = [
                    'error_code' => fake()->randomElement(['E001', 'E002', 'E003']),
                    'error_message' => fake()->sentence(),
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'failed',
                    'payment_processor' => 'gateway',
                ];
            } elseif ($paymentStatus === 'refunded') {
                // Fix: Ensure proper date range for refunded payments
                $originalPaidAt = fake()->dateTimeBetween('-30 days', '-15 days');
                $refundDate = fake()->dateTimeBetween($originalPaidAt, now());

                $externalId = strtoupper(Str::random(16));
                $paymentDetails = [
                    'transaction_id' => $externalId,
                    'refund_id' => 'REF-' . strtoupper(Str::random(8)),
                    'refund_reason' => fake()->sentence(),
                    'refund_date' => $refundDate->format('Y-m-d H:i:s'),
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'refunded',
                    'payment_processor' => 'gateway',
                ];

                $paidAt = $originalPaidAt; // Keep the original payment date
            }

            Payment::create([
                'booking_id' => $booking->id,
                'payment_code' => $paymentCode,
                'amount' => $booking->total_amount,
                'payment_method' => $paymentMethod,
                'status' => $paymentStatus,
                'external_id' => $externalId,
                'payment_details' => $paymentDetails,
                'paid_at' => $paidAt,
            ]);
        }
    }
}
