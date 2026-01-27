<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminAchievementController
 *
 * Handles admin panel endpoints for viewing all users' achievements
 */
class AdminAchievementController extends Controller
{
   
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Get all users' achievements and badge progress
     *
     * @group Admin - Loyalty
     * @authenticated
     *
     * GET /api/admin/users/achievements
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'total_points');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = User::with(['currentBadge'])
                ->where('role', User::ROLE_CUSTOMER);

            // Search functionality
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Add achievement count
            $query->withCount('achievements');

            // Sorting
            if (in_array($sortBy, ['total_points', 'total_cashback', 'name', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            } elseif ($sortBy === 'achievements_count') {
                $query->orderBy('achievements_count', $sortOrder);
            }

            $users = $query->paginate($perPage);

            $data = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_points' => $user->total_points,
                    'total_cashback' => $user->total_cashback,
                    'achievements_count' => $user->achievements_count,
                    'current_badge' => $user->currentBadge ? [
                        'id' => $user->currentBadge->id,
                        'name' => $user->currentBadge->name,
                        'level' => $user->currentBadge->level,
                        'icon' => $user->currentBadge->icon,
                        'color' => $user->currentBadge->color,
                    ] : null,
                    'member_since' => $user->created_at->format('Y-m-d'),
                    'achievement_progress' => $user->getAchievementProgressSummary(),
                    'badge_progress' => $user->getBadgeProgressSummary(),
                ];
            });

            return response()->json([
                'data' => $data,
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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve users data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get detailed view of a specific user's loyalty progress
     *
     * @group Admin - Loyalty
     * @authenticated
     *
     * GET /api/admin/users/{user}/loyalty
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user->load(['achievements', 'badges', 'currentBadge', 'purchases', 'cashbackTransactions']);

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_points' => $user->total_points,
                        'total_cashback' => $user->total_cashback,
                        'member_since' => $user->created_at,
                    ],
                    'achievements' => [
                        'total' => $user->achievements->count(),
                        'list' => $user->achievements->map(fn($a) => [
                            'id' => $a->id,
                            'name' => $a->name,
                            'type' => $a->type,
                            'tier' => $a->tier,
                            'points' => $a->points,
                            'unlocked_at' => $a->pivot->unlocked_at,
                        ]),
                    ],
                    'badges' => [
                        'current' => $user->currentBadge,
                        'earned' => $user->badges->map(fn($b) => [
                            'id' => $b->id,
                            'name' => $b->name,
                            'level' => $b->level,
                            'earned_at' => $b->pivot->earned_at,
                            'is_current' => $b->pivot->is_current,
                        ]),
                    ],
                    'activity' => [
                        'total_purchases' => $user->purchases()->completed()->count(),
                        'total_spent' => $user->purchases()->completed()->sum('amount'),
                        'recent_purchases' => $user->purchases()
                            ->latest()
                            ->limit(5)
                            ->get()
                            ->map(fn($p) => [
                                'id' => $p->id,
                                'amount' => $p->amount,
                                'status' => $p->status,
                                'created_at' => $p->created_at,
                            ]),
                        'cashback_transactions' => $user->cashbackTransactions()
                            ->latest()
                            ->limit(10)
                            ->get()
                            ->map(fn($t) => [
                                'id' => $t->id,
                                'amount' => $t->amount,
                                'status' => $t->status,
                                'processed_at' => $t->processed_at,
                            ]),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get loyalty program statistics
     *
     * @group Admin - Loyalty
     * @authenticated
     *
     * GET /api/admin/loyalty/stats
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::where('role', User::ROLE_CUSTOMER)->count(),
                    'with_achievements' => User::where('role', User::ROLE_CUSTOMER)
                        ->has('achievements')
                        ->count(),
                    'with_badges' => User::where('role', User::ROLE_CUSTOMER)
                        ->has('badges')
                        ->count(),
                ],
                'achievements' => [
                    'total' => Achievement::count(),
                    'active' => Achievement::active()->count(),
                    'total_unlocks' => DB::table('user_achievements')
                        ->whereNotNull('unlocked_at')
                        ->count(),
                    'by_type' => Achievement::select('type', DB::raw('count(*) as count'))
                        ->groupBy('type')
                        ->pluck('count', 'type'),
                ],
                'badges' => [
                    'total' => Badge::count(),
                    'active' => Badge::active()->count(),
                    'total_earned' => DB::table('user_badges')->count(),
                    'by_level' => Badge::select('level', DB::raw('count(*) as count'))
                        ->groupBy('level')
                        ->orderBy('level')
                        ->pluck('count', 'level'),
                ],
                'cashback' => [
                    'total_paid' => DB::table('cashback_transactions')
                        ->where('status', 'completed')
                        ->sum('amount'),
                    'pending' => DB::table('cashback_transactions')
                        ->where('status', 'pending')
                        ->sum('amount'),
                    'failed' => DB::table('cashback_transactions')
                        ->where('status', 'failed')
                        ->count(),
                    'success_rate' => $this->calculateSuccessRate(),
                ],
                'engagement' => [
                    'avg_achievements_per_user' => DB::table('user_achievements')
                        ->select(DB::raw('count(*) / count(distinct user_id) as avg'))
                        ->value('avg'),
                    'top_achievers' => User::where('role', User::ROLE_CUSTOMER)
                        ->orderBy('total_points', 'desc')
                        ->limit(5)
                        ->get(['id', 'name', 'total_points']),
                ],
            ];

            return response()->json(['data' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Calculate cashback success rate
     */
    protected function calculateSuccessRate(): float
    {
        $total = DB::table('cashback_transactions')->count();
        if ($total === 0) return 0;

        $successful = DB::table('cashback_transactions')
            ->where('status', 'completed')
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
