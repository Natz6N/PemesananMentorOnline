<?php

namespace App\Http\Controllers\api\V1\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreUserRequest;
use App\Services\FileStorageService;
use App\Http\Requests\UpdateUserRequest;
class AuthController extends Controller
{
    private FileStorageService $fileService;
    public function __construct(FileStorageService $fileService)
    {
        $this->fileService = $fileService;
    }
    /**
     * Login.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Email atau password salah'
                ], 401);
            }

            $user = Auth::user();

            // Hapus token lama (opsional, kalau hanya 1 login per user)
            $user->tokens()->delete();

            // Buat token baru
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->only(['id', 'name', 'email', 'role']) // aman!
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }

    }

    /**
     * register atau menambahkan ke dalam database.
     */
    public function register(StoreUserRequest $request)
    {
        try {

            $validatedData = $request->validated();

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'password' => Hash::make($validatedData['password']),
            ]);

            // buat token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        try {
            // Hapus token yang sedang digunakan
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'avatar' => $user->avatar ? url($user->avatar) : null,
                        'role' => $user->role,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * UpdateProfile.
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        try {
            $user = Auth::user();
            $validatedData = $request->validated();

            // Handle avatar upload jika ada
            if ($request->hasFile('avatar')) {
                // Hapus avatar lama jika ada
                if ($user->avatar) {
                    $this->fileService->deleteFile($user->avatar);
                }

                $uploadResult = $this->fileService->uploadFile(
                    $request->file('avatar'),
                    'avatars'
                );

                if ($uploadResult['success']) {
                    $validatedData['avatar'] = $uploadResult['data']['path'];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Avatar upload failed: ' . $uploadResult['message']
                    ], 400);
                }
            }

            // Update password jika ada
            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'avatar' => $user->avatar ? url($user->avatar) : null,
                        'role' => $user->role
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Cek password lama
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Hapus semua token (optional - force re-login)
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully. Please login again.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password change failed: ' . $e->getMessage()
            ], 500);
        }
    }
    // refresh token agar tidak dapat di eksploitasi
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();

            // Hapus token lama
            $request->user()->currentAccessToken()->delete();

            // Buat token baru
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed: ' . $e->getMessage()
            ], 500);
        }
    }

}
