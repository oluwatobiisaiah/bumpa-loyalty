<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementResource;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\UserLoyaltyResource;
use App\Models\User;
use App\Services\Loyalty\AchievementService;
use App\Services\Loyalty\BadgeService;
use App\Services\Payment\CashbackPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UserAchievementController
 *
 * Handles customer-facing achievement and loyalty endpoints
 */
class UserAchievementController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService,
        protected BadgeService $badgeService,
        protected CashbackPaymentService $cashbackService
    ) {}

    /**
     * Get user's achievement and badge progress
     *
     * @group Loyalty Program
     * @authenticated
     *
     * GET /api/users/{user}/achievements
     */
    public function index(Request $request, User $user): JsonResponse
    {
        // Ensure user can only access their own data (unless admin)
        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        try {
            $achievements = $this->achievementService->getUserAchievementProgress($user);
            $badgeProgress = $this->badgeService->getAllBadgeProgress($user);
            $badgeHistory = $this->badgeService->getBadgeHistory($user);
            $cashbackSummary = $this->cashbackService->getCashbackSummary($user);

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_points' => $user->total_points,
                        'total_cashback' => $user->total_cashback,
                    ],
                    'achievements' => [
                        'progress' => $achievements,
                        'summary' => $user->getAchievementProgressSummary(),
                        'recently_unlocked' => $this->achievementService->getRecentlyUnlocked($user),
                    ],
                    'badges' => [
                        // 'current' => $user->currentBadge ? new BadgeResource($user->currentBadge) : null,
                        'progress' => $badgeProgress,
                        'history' => $badgeHistory,
                        'summary' => $user->getBadgeProgressSummary(),
                    ],
                    'cashback' => $cashbackSummary,
                ],
                'message' => 'Loyalty data retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve loyalty data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get user's achievements only
     *
     * @group Loyalty Program
     * @authenticated
     *
     * GET /api/users/{user}/achievements/list
     */
    public function achievements(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $achievements = $this->achievementService->getUserAchievementProgress($user);

            return response()->json([
                'data' => $achievements,
                'summary' => $user->getAchievementProgressSummary(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve achievements',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get user's badges only
     *
     * @group Loyalty Program
     * @authenticated
     *
     * GET /api/users/{user}/badges
     */
    public function badges(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $badgeProgress = $this->badgeService->getAllBadgeProgress($user);

            return response()->json([
                'data' => $badgeProgress,
                // 'current_badge' => $user->currentBadge ? new BadgeResource($user->currentBadge) : null,
                'summary' => $user->getBadgeProgressSummary(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve badges',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get user's cashback summary
     *
     * @group Loyalty Program
     * @authenticated
     *
     * GET /api/users/{user}/cashback
     */
    public function cashback(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $summary = $this->cashbackService->getCashbackSummary($user);

            return response()->json([
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve cashback data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get authenticated user's loyalty dashboard
     *
     * @group Loyalty Program
     * @authenticated
     *
     * GET /api/loyalty/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        return $this->index($request, $request->user());
    }
}
