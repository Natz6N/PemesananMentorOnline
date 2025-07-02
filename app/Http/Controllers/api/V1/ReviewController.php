<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use App\Models\MentorProfile;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Review::with(['user', 'mentorProfile', 'booking']);

            // Filter by mentor profile
            if ($request->has('mentor_profile_id')) {
                $query->where('mentor_profile_id', $request->mentor_profile_id);
            }

            // Filter by rating
            if ($request->has('min_rating')) {
                $query->where('rating', '>=', $request->min_rating);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
            }

            // Sort reviews
            $sortField = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
            $allowedSortFields = ['created_at', 'rating'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortOrder);
            }

            $reviews = $query->paginate($request->per_page ?? 10);

            return ReviewResource::collection($reviews)
                ->additional([
                    'success' => true,
                    'message' => 'Daftar ulasan berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar ulasan',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function store(StoreReviewRequest $request)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk membuat ulasan
            if (!Gate::allows('create-review')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();
            $validated['user_id'] = Auth::id();

            // Verifikasi bahwa booking sudah selesai dan belum diulas
            $booking = Booking::findOrFail($validated['booking_id']);

            if ($booking->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat memberikan ulasan untuk booking Anda sendiri'
                ], 403);
            }

            if ($booking->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya booking yang sudah selesai yang dapat diulas'
                ], 422);
            }

            if ($booking->review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking ini sudah memiliki ulasan'
                ], 422);
            }

            // Set mentor_profile_id dari booking
            $validated['mentor_profile_id'] = $booking->mentor_profile_id;

            // Buat ulasan
            $review = Review::create($validated);

            // Update rating pada profil mentor
            $mentorProfile = MentorProfile::findOrFail($booking->mentor_profile_id);
            $mentorProfile->total_reviews += 1;

            // Hitung rata-rata rating baru
            $sumRating = Review::where('mentor_profile_id', $mentorProfile->id)->sum('rating');
            $mentorProfile->rating_average = $sumRating / $mentorProfile->total_reviews;

            $mentorProfile->save();

            DB::commit();

            return (new ReviewResource($review))
                ->additional([
                    'success' => true,
                    'message' => 'Ulasan berhasil dibuat'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat ulasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        try {
            return (new ReviewResource($review->load(['user', 'mentorProfile', 'booking'])))
                ->additional([
                    'success' => true,
                    'message' => 'Detail ulasan berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail ulasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk mengupdate ulasan
            if (!Gate::allows('update-review', $review)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            $validated = $request->validated();
            $oldRating = $review->rating;

            $review->update($validated);

            // Jika rating berubah, update rating pada profil mentor
            if (isset($validated['rating']) && $oldRating != $validated['rating']) {
                $mentorProfile = MentorProfile::findOrFail($review->mentor_profile_id);

                // Hitung rata-rata rating baru
                $sumRating = Review::where('mentor_profile_id', $mentorProfile->id)->sum('rating');
                $mentorProfile->rating_average = $sumRating / $mentorProfile->total_reviews;

                $mentorProfile->save();
            }

            DB::commit();

            return (new ReviewResource($review))
                ->additional([
                    'success' => true,
                    'message' => 'Ulasan berhasil diperbarui'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui ulasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk menghapus ulasan
            if (!Gate::allows('delete-review', $review)) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            DB::beginTransaction();

            // Simpan mentor_profile_id untuk update rating nanti
            $mentorProfileId = $review->mentor_profile_id;

            // Hapus ulasan
            $review->delete();

            // Update rating pada profil mentor
            $mentorProfile = MentorProfile::findOrFail($mentorProfileId);
            $mentorProfile->total_reviews -= 1;

            if ($mentorProfile->total_reviews > 0) {
                // Hitung rata-rata rating baru
                $sumRating = Review::where('mentor_profile_id', $mentorProfileId)->sum('rating');
                $mentorProfile->rating_average = $sumRating / $mentorProfile->total_reviews;
            } else {
                $mentorProfile->rating_average = 0;
            }

            $mentorProfile->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus ulasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews for a specific mentor.
     */
    public function mentorReviews($mentorId)
    {
        $reviews = Review::forMentor($mentorId)->latest()->paginate(10);

        return ReviewResource::collection($reviews)
            ->additional([
                'success' => true,
                'message' => 'Mentor reviews retrieved successfully'
            ]);
    }
}
