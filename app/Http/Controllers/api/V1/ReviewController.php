<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Review::query();

        // Filter by mentor_id if provided
        if ($request->has('mentor_id')) {
            $query->forMentor($request->mentor_id);
        }

        // Filter by min_rating if provided
        if ($request->has('min_rating')) {
            $query->highRating($request->min_rating);
        }

        // Filter by student_id if provided or if student is requesting their own reviews
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $reviews = $query->latest()->paginate($request->per_page ?? 10);

        return ReviewResource::collection($reviews)
            ->additional([
                'success' => true,
                'message' => 'Reviews retrieved successfully'
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
    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();
        $validated['student_id'] = Auth::id();

        $review = Review::create($validated);

        return (new ReviewResource($review))
            ->additional([
                'success' => true,
                'message' => 'Review created successfully'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        return (new ReviewResource($review))
            ->additional([
                'success' => true,
                'message' => 'Review retrieved successfully'
            ]);
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
        // Check if user is authorized to update this review
        $this->authorize('update', $review);

        $validated = $request->validated();
        $review->update($validated);

        return (new ReviewResource($review))
            ->additional([
                'success' => true,
                'message' => 'Review updated successfully'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        // Check if user is authorized to delete this review
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
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
