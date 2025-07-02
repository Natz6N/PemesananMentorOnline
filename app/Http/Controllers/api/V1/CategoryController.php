<?php
namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            // Search by name
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            // Sort results
            $sortField = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
            $allowedSortFields = ['name', 'created_at'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortOrder);
            }

            $categories = $query->paginate($request->per_page ?? 10);

            return CategoryResource::collection($categories)
                ->additional([
                    'success' => true,
                    'message' => 'Daftar kategori berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk membuat kategori
            if (!Gate::allows('manage-categories')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            $validated = $request->validated();

            // Periksa apakah kategori dengan nama yang sama sudah ada
            $existingCategory = Category::where('name', $validated['name'])->first();
            if ($existingCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori dengan nama ini sudah ada'
                ], 422);
            }

            if ($request->hasFile('icon')) {
                $validated['icon'] = $request->file('icon')->store('uploads/icons', 'public');
            }

            $category = Category::create($validated);

            return (new CategoryResource($category))
                ->additional([
                    'success' => true,
                    'message' => 'Kategori berhasil dibuat'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            return (new CategoryResource($category))
                ->additional([
                    'success' => true,
                    'message' => 'Detail kategori berhasil diambil'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk mengupdate kategori
            if (!Gate::allows('manage-categories')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            $category = Category::findOrFail($id);
            $validated = $request->validated();

            // Periksa apakah kategori dengan nama yang sama sudah ada (kecuali kategori ini sendiri)
            $existingCategory = Category::where('name', $validated['name'])
                ->where('id', '!=', $category->id)
                ->first();

            if ($existingCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori dengan nama ini sudah ada'
                ], 422);
            }

            if ($request->hasFile('icon')) {
                // Hapus icon lama jika ada
                if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                    Storage::disk('public')->delete($category->icon);
                }

                $validated['icon'] = $request->file('icon')->store('uploads/icons', 'public');
            }

            $category->update($validated);

            return (new CategoryResource($category))
                ->additional([
                    'success' => true,
                    'message' => 'Kategori berhasil diperbarui'
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Verifikasi bahwa user memiliki izin untuk menghapus kategori
            if (!Gate::allows('manage-categories')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'code' => 403
                ], 403);
            }

            // Periksa apakah kategori digunakan oleh mentor
            if ($category->mentorCategories()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena sedang digunakan oleh mentor'
                ], 422);
            }

            // Hapus icon jika ada
            if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                Storage::disk('public')->delete($category->icon);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
