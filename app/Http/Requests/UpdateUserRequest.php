<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            Rule::unique('users')->ignore($userId)
            ],
            'password' => 'sometimes|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ];
    }
    public function messages()
    {
        return [
            'name.string' => 'Name must be a string',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'phone.unique' => 'Phone number already exists',
            'password.min' => 'Password must be at least 6 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'avatar.image' => 'Avatar must be an image',
            'avatar.mimes' => 'Avatar must be jpeg, png, jpg, or gif',
            'avatar.max' => 'Avatar size must not exceed 5MB',
        ];
    }

}
