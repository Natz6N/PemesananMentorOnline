<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::query();

        // Filter by user role
        if (Auth::user()->role === 'student') {
            $query->whereHas('booking', function ($q) {
                $q->where('student_id', Auth::id());
            });
        } elseif (Auth::user()->role === 'mentor') {
            $query->whereHas('booking', function ($q) {
                $q->where('mentor_id', Auth::id());
            });
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payments = $query->with('booking')->latest()->paginate($request->per_page ?? 10);

        return PaymentResource::collection($payments)
            ->additional([
                'success' => true,
                'message' => 'Payments retrieved successfully'
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            $validated = $request->validated();

            // Get the booking
            $booking = Booking::findOrFail($validated['booking_id']);

            // Create payment record
            $payment = new Payment();
            $payment->booking_id = $booking->id;
            $payment->amount = $validated['amount'];
            $payment->payment_method = $validated['payment_method'];
            $payment->status = 'pending';
            $payment->payment_details = $validated['payment_details'] ?? null;
            $payment->generatePaymentCode();
            $payment->save();

            // Here you would integrate with a payment gateway
            // This is just a placeholder for the actual payment processing

            return (new PaymentResource($payment))
                ->additional([
                    'success' => true,
                    'message' => 'Payment initiated successfully',
                    'payment_redirect_url' => 'https://payment-gateway.example/pay/' . $payment->payment_code
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $payment = Payment::with('booking')->findOrFail($id);

            // Check authorization
            $booking = $payment->booking;
            if (!$booking ||
                (Auth::user()->role === 'student' && $booking->student_id !== Auth::id()) ||
                (Auth::user()->role === 'mentor' && $booking->mentor_id !== Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this payment'
                ], 403);
            }

            return (new PaymentResource($payment))
                ->additional([
                    'success' => true,
                    'message' => 'Payment retrieved successfully'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Process payment webhook from payment gateway.
     */
    public function webhook(Request $request)
    {
        try {
            // Log the webhook payload for debugging
            Log::info('Payment webhook received', $request->all());

            // Validate the webhook signature (implementation depends on the payment gateway)
            // $isValid = $this->validateWebhookSignature($request);
            // if (!$isValid) {
            //     return response()->json(['error' => 'Invalid signature'], 401);
            // }

            // Process the webhook payload
            $paymentCode = $request->input('payment_code');
            $status = $request->input('status');
            $externalId = $request->input('transaction_id');

            $payment = Payment::where('payment_code', $paymentCode)->first();

            if (!$payment) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Update payment status
            if ($status === 'success') {
                $payment->markAsPaid($externalId, $request->all());

                // Update the booking status if payment is successful
                $payment->booking->update(['status' => 'confirmed']);
            } else {
                $payment->status = 'failed';
                $payment->payment_details = $request->all();
                $payment->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Payment webhook error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
