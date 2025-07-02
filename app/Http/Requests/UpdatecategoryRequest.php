<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatecategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $this->route('category'),
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048', // ⬅️ ini penting
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori tidak boleh lebih dari 255 karakter.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.string' => 'Slug harus berupa teks.',
            'slug.max' => 'Slug tidak boleh lebih dari 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan oleh kategori lain.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'icon.string' => 'Icon harus berupa teks.',
            'icon.max' => 'Icon tidak boleh lebih dari 255 karakter.',
            'is_active.boolean' => 'Status aktif harus berupa nilai true atau false.',
        ];
    }

}
