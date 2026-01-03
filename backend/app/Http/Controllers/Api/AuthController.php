<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse|JsonResponse
    {
        try {
            // Stateless is important for API/SPA usage
            $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
            // Disable SSL verification for local development (fixes cURL error 60)
            $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            $googleUser = $driver->stateless()->user();

            $user = User::updateOrCreate(
                [
                    'email' => $googleUser->getEmail(),
                ],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            if (!$user->password) {
                // Optionally set a random password if it's null
            }

            $token = $user->createToken('API Token')->plainTextToken;

            // Redirect to frontend with token
            // TODO: Use env('FRONTEND_URL')
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

            return redirect("{$frontendUrl}/auth/callback?token={$token}");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Login Error: ' . $e->getMessage());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect("{$frontendUrl}/login?error=google_auth_failed&message=" . urlencode($e->getMessage()));
        }
    }
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }


    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'preferences' => 'sometimes|array',
        ]);

        if ($user->google_id && isset($validated['email'])) {
            unset($validated['email']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }
}
