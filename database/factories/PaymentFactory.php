<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $booking = Booking::factory()->create();
        $status = fake()->randomElement(['pending', 'paid', 'paid', 'paid', 'failed', 'refunded']);
        $paymentCode = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        $paymentMethods = ['credit_card', 'bank_transfer', 'e-wallet', 'paypal'];
        $paymentMethod = fake()->randomElement($paymentMethods);

        $paymentDetails = null;
        $paidAt = null;
        $externalId = null;

        if ($status === 'paid') {
            $paidAt = fake()->dateTimeBetween('-10 days', 'now');
            $externalId = strtoupper(Str::random(16));
            $paymentDetails = [
                'transaction_id' => $externalId,
                'payment_method' => $paymentMethod,
                'payment_date' => $paidAt->format('Y-m-d H:i:s'),
                'payment_status' => 'success',
                'payment_processor' => 'gateway',
            ];
        } elseif ($status === 'failed') {
            $paymentDetails = [
                'error_code' => fake()->randomElement(['E001', 'E002', 'E003']),
                'error_message' => fake()->sentence(),
                'payment_method' => $paymentMethod,
                'payment_status' => 'failed',
                'payment_processor' => 'gateway',
            ];
        } elseif ($status === 'refunded') {
            $paidAt = fake()->dateTimeBetween('-20 days', '-10 days');
            $externalId = strtoupper(Str::random(16));
            $paymentDetails = [
                'transaction_id' => $externalId,
                'refund_id' => 'REF-' . strtoupper(Str::random(8)),
                'refund_reason' => fake()->sentence(),
                'refund_date' => fake()->dateTimeBetween('-10 days', 'now')->format('Y-m-d H:i:s'),
                'payment_method' => $paymentMethod,
                'payment_status' => 'refunded',
                'payment_processor' => 'gateway',
            ];
        }

        return [
            'booking_id' => $booking->id,
            'payment_code' => $paymentCode,
            'amount' => $booking->total_amount,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'external_id' => $externalId,
            'payment_details' => $paymentDetails,
            'paid_at' => $paidAt,
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'external_id' => null,
            'payment_details' => null,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paid(): static
    {
        $paidAt = fake()->dateTimeBetween('-10 days', 'now');
        $externalId = strtoupper(Str::random(16));
        $paymentMethod = fake()->randomElement(['credit_card', 'bank_transfer', 'e-wallet', 'paypal']);

        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'external_id' => $externalId,
            'payment_details' => [
                'transaction_id' => $externalId,
                'payment_method' => $paymentMethod,
                'payment_date' => $paidAt->format('Y-m-d H:i:s'),
                'payment_status' => 'success',
                'payment_processor' => 'gateway',
            ],
            'paid_at' => $paidAt,
        ]);
    }
}
