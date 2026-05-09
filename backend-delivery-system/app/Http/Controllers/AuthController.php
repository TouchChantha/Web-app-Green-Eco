<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user and return a short Sanctum token.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'nullable|in:admin,driver',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'phone'     => $data['phone'] ?? null,
            'role'      => $data['role'] ?? 'driver',
            'is_active' => true,
        ]);

        // Issue a short Sanctum token directly — no session needed
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->respondWithToken($token, $user, 201);
    }

    /**
     * Login and return a short Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user and verify password manually — avoids session dependency
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated.',
            ], 403);
        }

        // Keep the role column in sync with the Spatie role assignment
        $spatieRole = $user->getRoleNames()->first();
        if ($spatieRole && $user->role !== $spatieRole) {
            $user->update(['role' => $spatieRole]);
        }

        // Revoke old tokens and issue a fresh short token
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->respondWithToken($token, $user);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    /**
     * Get the authenticated user's roles and permissions.
     */
    public function permissions(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user'        => $user->only('id', 'name', 'email', 'role'),
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Refresh — revoke the current token and issue a fresh one.
     * Matches the "Refresh JWT token" use case (shared by both roles).
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke the current token and issue a brand-new one
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->respondWithToken($token, $user);
    }

    /**
     * Logout — revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    // -------------------------------------------------------------------------

    private function respondWithToken(string $token, User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'is_active' => $user->is_active,
            ],
        ], $status);
    }
}
