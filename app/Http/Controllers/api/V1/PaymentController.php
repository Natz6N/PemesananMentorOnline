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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['booking.mentorProfile', 'booking.user']);

            // Filter berdasarkan peran pengguna
            $user = Auth::user();

            if ($user->role === 'student') {
                $query->whereHas('booking', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } elseif ($user->role === 'mentor') {
                $mentorProfile = $user->mentorProfile;
                if (!$mentorProfile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Profil mentor tidak ditemukan'
                    ], 404);
                }

                $query->whereHas('booking', function ($q) use ($mentorProfile) {
                    $q->where('mentor_profile_id', $mentorProfile->id);
                });
            }
            // Admin dapat melihat semua pembayaran

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan rentang tanggal
            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
            }

            // Filter berdasarkan booking_id
            if ($request->has('booking_id')) {
                $query->where('booking_id', $request->booking_id);
            }

            // Urutkan
            $sortField = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $allowedSortFields = ['created_at', 'status', 'amount'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortOrder);
            }

            $payments = $query->paginate($request->per_page ?? 10);

            return PaymentResource::collection($payments)
                ->additional([
                    'success' => true,
                    'message' => 'Daftar pembayaran berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk membuat pembayaran
            if (!Gate::allows('create-payment')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();

            // Verifikasi booking
            $booking = Booking::findOrFail($validated['booking_id']);

            // Verifikasi bahwa booking milik user yang login
            if ($booking->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat membayar booking Anda sendiri'
                ], 403);
            }

            // Verifikasi bahwa booking belum dibayar
            if (Payment::where('booking_id', $booking->id)->where('status', 'completed')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking ini sudah dibayar'
                ], 422);
            }

            // Verifikasi bahwa jumlah pembayaran sesuai dengan total harga booking
            if ($validated['amount'] != $booking->total_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran harus sama dengan total harga booking'
                ], 422);
            }

            // Set status pembayaran (asumsi payment gateway integration)
            $validated['status'] = 'pending';
            $validated['payment_date'] = now();

            // Generate payment code
            $validated['payment_code'] = 'PAY-' . strtoupper(substr(uniqid(), 0, 8));

            // Buat pembayaran
            $payment = Payment::create($validated);

            // Simulasi proses pembayaran berhasil
            // In a real application, this would be handled by a webhook from the payment gateway
            if ($request->has('simulate_success') && $request->simulate_success && env('APP_ENV') !== 'production') {
                $payment->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);

                // Update status booking menjadi confirmed
                $booking->update(['status' => 'confirmed']);
            }

            DB::commit();

            return (new PaymentResource($payment))
                ->additional([
                    'success' => true,
                    'message' => 'Pembayaran berhasil dibuat'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        try {
            // Verifikasi bahwa user memiliki akses ke pembayaran ini
            if (!Gate::allows('view-payment', $payment)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            return (new PaymentResource($payment->load(['booking.mentorProfile', 'booking.user'])))
                ->additional([
                    'success' => true,
                    'message' => 'Detail pembayaran berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk mengupdate pembayaran
            if (!Gate::allows('update-payment', $payment)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();
            $originalStatus = $payment->status;

            // Jika mengubah status pembayaran
            if (isset($validated['status']) && $validated['status'] !== $originalStatus) {
                $newStatus = $validated['status'];

                // Hanya admin yang dapat mengubah status pembayaran
                if (Auth::user()->role !== 'admin') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya admin yang dapat mengubah status pembayaran'
                    ], 403);
                }

                // Pembayaran yang sudah completed tidak dapat diubah
                if ($originalStatus === 'completed' && $newStatus !== 'completed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran yang sudah completed tidak dapat diubah'
                    ], 422);
                }

                // Jika pembayaran menjadi completed, update completed_at dan status booking
                if ($newStatus === 'completed' && $originalStatus !== 'completed') {
                    $validated['completed_at'] = now();

                    // Update status booking menjadi confirmed
                    $booking = $payment->booking;
                    $booking->update(['status' => 'confirmed']);
                }
            }

            $payment->update($validated);

            DB::commit();

            return (new PaymentResource($payment))
                ->additional([
                    'success' => true,
                    'message' => 'Pembayaran berhasil diperbarui'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk menghapus pembayaran
            if (!Gate::allows('delete-payment', $payment)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            // Hanya admin yang dapat menghapus pembayaran
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya admin yang dapat menghapus pembayaran'
                ], 403);
            }

            // Pembayaran dengan status completed tidak dapat dihapus
            if ($payment->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran yang sudah completed tidak dapat dihapus'
                ], 422);
            }

            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
