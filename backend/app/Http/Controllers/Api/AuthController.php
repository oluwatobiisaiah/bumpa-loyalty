<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * AuthController
 *
 * Handles customer authentication
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * @group Authentication
     * POST /api/v1/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', User::ROLE_CUSTOMER)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_points' => $user->total_points,
                    'total_cashback' => $user->total_cashback,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Register
     *
     * @group Authentication
     * POST /api/v1/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_CUSTOMER,
            'total_points' => 0,
            'total_cashback' => 0,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Logout
     *
     * @group Authentication
     * @authenticated
     * POST /api/v1/logout
     */
    public function logout(Request $request): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Refresh JWT token
     *
     * @group Authentication
     * @authenticated
     * POST /api/v1/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'token' => JWTAuth::refresh(),
                'token_type' => 'bearer',
            ],
        ]);
    }

    /**
     * Get authenticated user
     *
     * @group Authentication
     * @authenticated
     * GET /api/v1/user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('currentBadge');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'total_points' => $user->total_points,
                'total_cashback' => $user->total_cashback,
                'current_badge' => $user->currentBadge ? [
                    'id' => $user->currentBadge->id,
                    'name' => $user->currentBadge->name,
                    'level' => $user->currentBadge->level,
                    'icon' => $user->currentBadge->icon,
                ] : null,
                'achievements_count' => $user->achievements()->count(),
                'badges_count' => $user->badges()->count(),
            ],
        ]);
    }
}

