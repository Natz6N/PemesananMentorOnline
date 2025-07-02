<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MentorProfile;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\BookingCreated;
use App\Events\BookingUpdated;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::query();

        // Filter by student_id if provided or if student is requesting
        if ($request->has('student_id') || Auth::user()->role === 'student') {
            $studentId = $request->student_id ?? Auth::id();
            $query->where('student_id', $studentId);
        }

        // Filter by mentor_id if provided or if mentor is requesting
        if ($request->has('mentor_id') || Auth::user()->role === 'mentor') {
            $mentorId = $request->mentor_id ?? Auth::id();
            $query->where('mentor_id', $mentorId);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('scheduled_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('scheduled_at', '<=', $request->end_date);
        }

        $bookings = $query->with(['student', 'mentor', 'mentorProfile'])
            ->latest()
            ->paginate($request->per_page ?? 10);

        return BookingResource::collection($bookings)
            ->additional([
                'success' => true,
                'message' => 'Bookings retrieved successfully'
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
        // Pastikan hanya student yang bisa membuat booking
        if (Auth::user()->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya student yang dapat membuat booking'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['student_id'] = Auth::id();
            $validated['status'] = 'pending';
            $validated['booking_code'] = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 0, 6));

            // Cek ketersediaan mentor
            $mentorProfile = MentorProfile::findOrFail($validated['mentor_profile_id']);
            if (!$mentorProfile->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mentor tidak tersedia untuk booking saat ini'
                ], 400);
            }

            // Set mentor_id dari mentor_profile
            $validated['mentor_id'] = $mentorProfile->user_id;

            // Buat booking
            $booking = Booking::create($validated);

            // Load relasi untuk broadcast event
            $booking->load(['student', 'mentor', 'mentorProfile']);

            // Broadcast event BookingCreated
            event(new BookingCreated($booking));

            DB::commit();

            return (new BookingResource($booking))
                ->additional([
                    'success' => true,
                    'message' => 'Booking created successfully'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        // Check if user is authorized to view this booking
        $this->authorize('view', $booking);

        $booking->load(['student', 'mentor', 'mentorProfile']);

        return (new BookingResource($booking))
            ->additional([
                'success' => true,
                'message' => 'Booking retrieved successfully'
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
        // Check if user is authorized to update this booking
        $this->authorize('update', $booking);

        try {
            $validated = $request->validated();

            // Simpan status lama untuk event
            $oldStatus = $booking->status;

            // Update booking
            $booking->update($validated);

            // Reload booking dengan relasi
            $booking->load(['student', 'mentor', 'mentorProfile']);

            // Jika status berubah, broadcast event BookingUpdated
            if ($oldStatus !== $booking->status) {
                event(new BookingUpdated($booking, $oldStatus));
            }

            return (new BookingResource($booking))
                ->additional([
                    'success' => true,
                    'message' => 'Booking updated successfully'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        // Check if user is authorized to cancel this booking
        $this->authorize('delete', $booking);

        try {
            // Simpan status lama untuk event
            $oldStatus = $booking->status;

            // Update status menjadi cancelled
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => request('cancellation_reason', 'Dibatalkan oleh pengguna')
            ]);

            // Reload booking dengan relasi
            $booking->load(['student', 'mentor', 'mentorProfile']);

            // Broadcast event BookingUpdated
            event(new BookingUpdated($booking, $oldStatus));

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking',
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
