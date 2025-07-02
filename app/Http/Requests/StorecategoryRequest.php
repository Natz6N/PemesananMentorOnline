<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorecategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'is_active' => 'boolean',
        ];
    }
    public function messages()
{
    return [
        'name.required' => 'Nama kategori wajib diisi.',
        'name.string' => 'Nama kategori harus berupa teks.',
        'name.max' => 'Nama kategori maksimal 255 karakter.',

        'slug.required' => 'Slug wajib diisi.',
        'slug.string' => 'Slug harus berupa teks.',
        'slug.max' => 'Slug maksimal 255 karakter.',
        'slug.unique' => 'Slug sudah digunakan. Silakan pilih slug lain yang unik.',

        'description.string' => 'Deskripsi harus berupa teks.',

        'icon.file' => 'Ikon harus berupa file.',
        'icon.mimes' => 'Format ikon harus berupa jpg, jpeg, png, atau svg.',
        'icon.max' => 'Ukuran ikon maksimal 2MB (2048 KB).',

        'is_active.boolean' => 'Status aktif harus bernilai true atau false.',
    ];
}

}
