<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\MentorCategory;
use App\Http\Requests\StoreMentorProfileRequest;
use App\Http\Requests\UpdateMentorProfileRequest;
use App\Http\Resources\MentorProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MentorProfileController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MentorProfile::with(['user', 'categories'])
            ->approved()
            ->when($request->available, function ($q) {
                return $q->available();
            });

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by hourly rate range
        if ($request->has('min_rate')) {
            $query->where('hourly_rate', '>=', $request->min_rate);
        }

        if ($request->has('max_rate')) {
            $query->where('hourly_rate', '<=', $request->max_rate);
        }

        // Filter by minimum rating
        if ($request->has('min_rating')) {
            $query->where('rating_average', '>=', $request->min_rating);
        }

        // Search by expertise
        if ($request->has('expertise')) {
            $query->where('expertise', 'like', '%' . $request->expertise . '%');
        }

        // Sort results
        $sortField = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
        $allowedSortFields = ['hourly_rate', 'rating_average', 'experience_years', 'created_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        }

        $mentors = $query->paginate($request->per_page ?? 10);

        return MentorProfileResource::collection($mentors)
            ->additional([
                'success' => true,
                'message' => 'Mentor profiles retrieved successfully'
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMentorProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['status'] = 'pending'; // New profiles need admin approval
            $validated['rating_average'] = 0;
            $validated['total_reviews'] = 0;
            $validated['total_sessions'] = 0;
            $validated['is_available'] = false;

            $categoryIds = $validated['category_ids'];
            unset($validated['category_ids']);

            $mentorProfile = MentorProfile::create($validated);

            // Attach categories
            foreach ($categoryIds as $categoryId) {
                MentorCategory::create([
                    'mentor_profile_id' => $mentorProfile->id,
                    'category_id' => $categoryId
                ]);
            }

            DB::commit();

            return (new MentorProfileResource($mentorProfile))
                ->additional([
                    'success' => true,
                    'message' => 'Mentor profile created successfully and pending approval'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create mentor profile',
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
            $mentorProfile = MentorProfile::with(['user', 'categories', 'availabilities'])
                ->findOrFail($id);

            return (new MentorProfileResource($mentorProfile))
                ->additional([
                    'success' => true,
                    'message' => 'Mentor profile retrieved successfully'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mentor profile not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMentorProfileRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $mentorProfile = MentorProfile::findOrFail($id);

            // Check authorization
            if ($mentorProfile->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this profile'
                ], 403);
            }

            $validated = $request->validated();

            // Handle categories if they're being updated
            if (isset($validated['category_ids'])) {
                $categoryIds = $validated['category_ids'];
                unset($validated['category_ids']);

                // Delete existing categories
                MentorCategory::where('mentor_profile_id', $mentorProfile->id)->delete();

                // Add new categories
                foreach ($categoryIds as $categoryId) {
                    MentorCategory::create([
                        'mentor_profile_id' => $mentorProfile->id,
                        'category_id' => $categoryId
                    ]);
                }
            }

            $mentorProfile->update($validated);

            DB::commit();

            return (new MentorProfileResource($mentorProfile))
                ->additional([
                    'success' => true,
                    'message' => 'Mentor profile updated successfully'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update mentor profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $mentorProfile = MentorProfile::findOrFail($id);

            // Check authorization
            if ($mentorProfile->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this profile'
                ], 403);
            }

            // Delete related categories
            MentorCategory::where('mentor_profile_id', $mentorProfile->id)->delete();

            // Delete the profile
            $mentorProfile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mentor profile deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete mentor profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
