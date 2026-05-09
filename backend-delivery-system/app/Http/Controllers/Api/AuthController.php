<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Login — validate credentials and return a JWT token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        // Keep role column in sync with Spatie role
        $spatieRole = $user->getRoleNames()->first();
        if ($spatieRole && $user->role !== $spatieRole) {
            $user->update(['role' => $spatieRole]);
            $user->refresh();
        }

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user);
    }

    /**
     * Register a new user (admin use — creates driver accounts).
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

        $roleName = $data['role'] ?? 'driver';

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'phone'     => $data['phone'] ?? null,
            'role'      => $roleName,
            'is_active' => true,
        ]);

        // Assign Spatie role
        $spatieRole = Role::where('name', $roleName)->where('guard_name', 'api')->first();
        if ($spatieRole) {
            $user->syncRoles([$spatieRole]);
        }

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user, 201);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Refresh JWT token — shared use case (admin + driver).
     */
    public function refresh(): JsonResponse
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            $user     = JWTAuth::setToken($newToken)->toUser();

            return $this->respondWithToken($newToken, $user);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired and can no longer be refreshed.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not refresh token.'], 500);
        }
    }

    /**
     * Logout — invalidate the JWT token (blacklist it).
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::parseToken()->invalidate();
        } catch (JWTException) {
            // Token already invalid — still treat as successful logout
        }

        return response()->json(['message' => 'Successfully logged out.']);
    }

    // -------------------------------------------------------------------------

    private function respondWithToken(string $token, User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60, // seconds
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
