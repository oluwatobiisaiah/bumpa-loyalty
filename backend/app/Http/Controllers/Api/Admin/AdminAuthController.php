<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AdminAuthController
 *
 * Handles admin authentication
 */
class AdminAuthController extends Controller
{
    /**
     * Admin login
     *
     * @group Admin Authentication
     * POST /api/v1/admin/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', User::ROLE_ADMIN)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid admin credentials.'],
            ]);
        }

        $token = $user->createToken('admin_token', ['admin'])->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Admin logout successful',
        ]);
    }

    /**
     * Get authenticated admin
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    /**
     * Get paginated list of users
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::where('role', User::ROLE_CUSTOMER)
            ->with(['currentBadge', 'achievements'])
            ->withCount(['achievements' => function ($q) {
                $q->whereNotNull('user_achievements.unlocked_at');
            }]);

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'total_points');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['total_points', 'total_cashback', 'name', 'created_at', 'achievements_count'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'achievements_count') {
                $query->orderBy('achievements_count', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        $transformedUsers = $users->getCollection()->map(function ($user) {
            $totalAchievements = Achievement::active()->count();
            $unlockedCount = $user->achievements_count;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_points' => $user->total_points,
                'total_cashback' => $user->total_cashback,
                'achievements_count' => $unlockedCount,
                'current_badge' => $user->currentBadge ? [
                    'id' => $user->currentBadge->id,
                    'name' => $user->currentBadge->name,
                    'icon' => $user->currentBadge->icon,
                ] : null,
                'member_since' => $user->created_at->format('Y-m-d'),
                'achievement_progress' => [
                    'total_achievements' => $totalAchievements,
                    'unlocked_achievements' => $unlockedCount,
                    'completion_percentage' => $totalAchievements > 0
                        ? round(($unlockedCount / $totalAchievements) * 100, 1)
                        : 0,
                ],
            ];
        });

        return response()->json([
            'data' => $transformedUsers,
            'meta' => [
                'current_page' => $users->currentPage(),
                'from' => $users->firstItem(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'to' => $users->lastItem(),
                'total' => $users->total(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
        ]);
    }
}
