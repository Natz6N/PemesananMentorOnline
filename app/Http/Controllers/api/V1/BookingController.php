<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MentorAvailabilitie;
use App\Models\MentorProfile;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Events\BookingCreated;
use App\Events\BookingUpdated;

class BookingController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query
        $query = Booking::with(['mentorProfile.user', 'mentorAvailabilitie', 'payment', 'review']);

        // Filter based on user role
        if ($user->role === 'student') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'mentor') {
            $mentorProfile = $user->mentorProfile;
            if (!$mentorProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil mentor tidak ditemukan'
                ], 404);
            }
            $query->where('mentor_profile_id', $mentorProfile->id);
        }
        // Admin dapat melihat semua booking

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->has('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        // Urutkan
        $sortField = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $allowedSortFields = ['session_date', 'created_at', 'status'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        }

        $bookings = $query->paginate($request->per_page ?? 10);

        return BookingResource::collection($bookings)
            ->additional([
                'success' => true,
                'message' => 'Daftar booking berhasil diambil'
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk membuat booking
            if (!Gate::allows('create-booking')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['status'] = 'pending'; // Default status for new bookings

            // Verify mentor availability
            $mentorProfile = MentorProfile::findOrFail($validated['mentor_profile_id']);
            $mentorAvailability = MentorAvailabilitie::findOrFail($validated['mentor_availabilitie_id']);

            if ($mentorAvailability->mentor_profile_id !== $mentorProfile->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ketersediaan tidak terkait dengan mentor yang dipilih'
                ], 422);
            }

            // Check if the mentor is available on the requested date and time
            $dayOfWeek = strtolower(date('l', strtotime($validated['session_date'])));

            if ($dayOfWeek !== $mentorAvailability->day_of_week) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mentor tidak tersedia pada hari yang dipilih'
                ], 422);
            }

            // Validate session time is within availability time
            $sessionStartTime = date('H:i:s', strtotime($validated['session_time']));
            $sessionEndTime = date('H:i:s', strtotime($validated['session_time']) + ($validated['duration'] * 60));

            if ($sessionStartTime < $mentorAvailability->start_time ||
                $sessionEndTime > $mentorAvailability->end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu sesi tidak dalam rentang ketersediaan mentor'
                ], 422);
            }

            // Check for booking conflicts
            $conflictingBooking = Booking::where('mentor_profile_id', $validated['mentor_profile_id'])
                ->where('session_date', $validated['session_date'])
                ->where(function($query) use ($sessionStartTime, $sessionEndTime) {
                    $query->where(function($q) use ($sessionStartTime, $sessionEndTime) {
                        $q->where('session_time', '<=', $sessionStartTime)
                          ->whereRaw("ADDTIME(session_time, SEC_TO_TIME(duration * 60)) > ?", [$sessionStartTime]);
                    })->orWhere(function($q) use ($sessionStartTime, $sessionEndTime) {
                        $q->where('session_time', '<', $sessionEndTime)
                          ->where('session_time', '>=', $sessionStartTime);
                    });
                })
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($conflictingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mentor sudah memiliki booking pada waktu yang dipilih'
                ], 422);
            }

            // Calculate total price based on mentor's hourly rate and session duration
            $hourlyRate = $mentorProfile->hourly_rate;
            $durationInHours = $validated['duration'] / 60;
            $validated['total_price'] = $hourlyRate * $durationInHours;

            $booking = Booking::create($validated);

            // Trigger event for notification
            event(new BookingCreated($booking));

            DB::commit();

            return (new BookingResource($booking))
                ->additional([
                    'success' => true,
                    'message' => 'Booking berhasil dibuat'
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        // Verifikasi bahwa user memiliki akses ke booking ini
        if (!Gate::allows('view-booking', $booking)) {
            return response()->json([
                'message' => 'Unauthorized.',
                'code' => 403
            ], 403);
        }

        return (new BookingResource($booking->load(['mentorProfile.user', 'mentorAvailabilitie', 'payment', 'review'])))
            ->additional([
                'success' => true,
                'message' => 'Detail booking berhasil diambil'
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk mengupdate booking
            if (!Gate::allows('update-booking', $booking)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();
            $originalStatus = $booking->status;

            // Jika mengubah status booking
            if (isset($validated['status']) && $validated['status'] !== $originalStatus) {
                $newStatus = $validated['status'];

                // Validasi perubahan status
                $user = Auth::user();

                // Student hanya dapat membatalkan booking (cancel)
                if ($user->role === 'student' && $newStatus !== 'cancelled') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student hanya dapat membatalkan booking'
                    ], 422);
                }

                // Mentor hanya dapat mengkonfirmasi, menolak, atau menyelesaikan booking
                if ($user->role === 'mentor' && !in_array($newStatus, ['confirmed', 'rejected', 'completed'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mentor hanya dapat mengkonfirmasi, menolak, atau menyelesaikan booking'
                    ], 422);
                }

                // Booking yang sudah selesai, dibatalkan, atau ditolak tidak dapat diubah lagi
                if (in_array($originalStatus, ['completed', 'cancelled', 'rejected'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Booking yang sudah {$originalStatus} tidak dapat diubah lagi"
                    ], 422);
                }

                // Booking yang belum dikonfirmasi tidak dapat diselesaikan
                if ($newStatus === 'completed' && $originalStatus !== 'confirmed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking harus dikonfirmasi terlebih dahulu sebelum diselesaikan'
                    ], 422);
                }
            }

            $booking->update($validated);

            // Trigger event for notification if status changed
            if (isset($validated['status']) && $validated['status'] !== $originalStatus) {
                event(new BookingUpdated($booking));
            }

            DB::commit();

            return (new BookingResource($booking))
                ->additional([
                    'success' => true,
                    'message' => 'Booking berhasil diperbarui'
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk menghapus booking
            if (!Gate::allows('delete-booking', $booking)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            // Hanya admin yang dapat menghapus booking
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya admin yang dapat menghapus booking'
                ], 403);
            }

            // Booking tidak dapat dihapus jika sudah ada pembayaran
            if ($booking->payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking tidak dapat dihapus karena sudah ada pembayaran'
                ], 422);
            }

            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm a booking (for mentors).
     */
    public function confirmBooking(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Check if user is the mentor of this booking
            if (Auth::id() !== $booking->mentor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to confirm this booking'
                ], 403);
            }

            // Check if booking is in pending status
            if ($booking->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be confirmed'
                ], 400);
            }

            // Simpan status lama untuk event
            $oldStatus = $booking->status;

            // Update status
            $booking->update(['status' => 'confirmed']);

            // Reload booking dengan relasi
            $booking->load(['student', 'mentor', 'mentorProfile']);

            // Broadcast event BookingUpdated
            event(new BookingUpdated($booking, $oldStatus));

            return (new BookingResource($booking))
                ->additional([
                    'success' => true,
                    'message' => 'Booking confirmed successfully'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a booking as completed (for mentors).
     */
    public function completeBooking(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Check if user is the mentor of this booking
            if (Auth::id() !== $booking->mentor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to complete this booking'
                ], 403);
            }

            // Check if booking is in confirmed status
            if ($booking->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be marked as completed'
                ], 400);
            }

            // Simpan status lama untuk event
            $oldStatus = $booking->status;

            // Update status
            $booking->update(['status' => 'completed']);

            // Reload booking dengan relasi
            $booking->load(['student', 'mentor', 'mentorProfile']);

            // Broadcast event BookingUpdated
            event(new BookingUpdated($booking, $oldStatus));

            return (new BookingResource($booking))
                ->additional([
                    'success' => true,
                    'message' => 'Booking completed successfully'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
