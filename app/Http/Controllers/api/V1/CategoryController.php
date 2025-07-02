<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            return CategoryResource::collection($categories);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon')->store('uploads/icons', 'public');
            }

            $category = Category::create($data);
            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            $data = $request->validated();

            if ($request->hasFile('icon')) {
                // Hapus icon lama jika ada
                if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                    Storage::disk('public')->delete($category->icon);
                }

                $data['icon'] = $request->file('icon')->store('uploads/icons', 'public');
            }

            $category->update($data);
            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
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
