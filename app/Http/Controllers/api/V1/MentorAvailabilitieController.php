<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\MentorAvailabilitie;
use App\Models\MentorProfile;
use App\Http\Requests\StoreMentorAvailabilitieRequest;
use App\Http\Requests\UpdateMentorAvailabilitieRequest;
use App\Http\Resources\MentorAvailabilityResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MentorAvailabilitieController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(MentorAvailabilitie::class, 'mentorAvailabilitie');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Jika admin, tampilkan semua ketersediaan
            if (Auth::user()->role === 'admin') {
                $availabilities = MentorAvailabilitie::with('mentorProfile')->get();
            }
            // Jika mentor, tampilkan hanya ketersediaan mereka
            elseif (Auth::user()->role === 'mentor') {
                $mentorProfile = Auth::user()->mentorProfile;
                if (!$mentorProfile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Profil mentor tidak ditemukan'
                    ], 404);
                }
                $availabilities = $mentorProfile->availabilities()->get();
            }
            // Jika student, hanya ketersediaan yang aktif
            else {
                $availabilities = MentorAvailabilitie::with('mentorProfile')
                    ->where('is_active', true)
                    ->get();
            }

            return MentorAvailabilityResource::collection($availabilities)
                ->additional([
                    'success' => true,
                    'message' => 'Ketersediaan mentor berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get availabilities for a specific mentor
     */
    public function getMentorAvailability($mentorId)
    {
        try {
            $mentorProfile = MentorProfile::findOrFail($mentorId);
            $availabilities = $mentorProfile->availabilities()
                ->when(Auth::user()->role === 'student', function($query) {
                    return $query->where('is_active', true);
                })
                ->get();

            return MentorAvailabilityResource::collection($availabilities)
                ->additional([
                    'success' => true,
                    'message' => 'Ketersediaan mentor berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set availabilities for a specific mentor
     */
    public function setMentorAvailability(Request $request, $mentorId)
    {
        try {
            DB::beginTransaction();

            $mentorProfile = MentorProfile::findOrFail($mentorId);

            // Authorisasi dengan gate
            if (!Auth::user()->can('manage-availability') ||
                (Auth::user()->role === 'mentor' && $mentorProfile->user_id !== Auth::id())) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            // Validasi request
            $request->validate([
                'availabilities' => 'required|array',
                'availabilities.*.day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'availabilities.*.start_time' => 'required|date_format:H:i:s',
                'availabilities.*.end_time' => 'required|date_format:H:i:s|after:availabilities.*.start_time',
                'availabilities.*.is_active' => 'boolean',
            ]);

            // Delete existing availabilities
            $mentorProfile->availabilities()->delete();

            // Create new availabilities
            $newAvailabilities = [];
            foreach ($request->availabilities as $availability) {
                $newAvailabilities[] = $mentorProfile->availabilities()->create([
                    'day_of_week' => $availability['day_of_week'],
                    'start_time' => $availability['start_time'],
                    'end_time' => $availability['end_time'],
                    'is_active' => $availability['is_active'] ?? true,
                ]);
            }

            DB::commit();

            return MentorAvailabilityResource::collection($newAvailabilities)
                ->additional([
                    'success' => true,
                    'message' => 'Ketersediaan mentor berhasil diperbarui'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMentorAvailabilitieRequest $request)
    {
        try {
            $validated = $request->validated();

            // Check if mentor_profile_id belongs to the authenticated user (if mentor)
            if (Auth::user()->role === 'mentor') {
                $mentorProfile = Auth::user()->mentorProfile;
                if (!$mentorProfile || $mentorProfile->id != $validated['mentor_profile_id']) {
                    return response()->json([
                        'message' => 'Unauthorized.',
                        'code' => 403
                    ], 403);
                }
            }

            $availability = MentorAvailabilitie::create($validated);

            return (new MentorAvailabilityResource($availability))
                ->additional([
                    'success' => true,
                    'message' => 'Ketersediaan mentor berhasil dibuat'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MentorAvailabilitie $mentorAvailabilitie)
    {
        try {
            return (new MentorAvailabilityResource($mentorAvailabilitie))
                ->additional([
                    'success' => true,
                    'message' => 'Ketersediaan mentor berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMentorAvailabilitieRequest $request, MentorAvailabilitie $mentorAvailabilitie)
    {
        try {
            $validated = $request->validated();
            $mentorAvailabilitie->update($validated);

            return (new MentorAvailabilityResource($mentorAvailabilitie))
                ->additional([
                    'success' => true,
                    'message' => 'Ketersediaan mentor berhasil diperbarui'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MentorAvailabilitie $mentorAvailabilitie)
    {
        try {
            $mentorAvailabilitie->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ketersediaan mentor berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus ketersediaan mentor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
