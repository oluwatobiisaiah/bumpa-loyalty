<?php

namespace App\Services\Loyalty;

use App\Models\User;
use App\Models\Badge;
use App\Events\BadgeUnlocked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BadgeService
 *
 * Manages badge progression, awarding, and tracking
 */
class BadgeService
{
    /**
     * Check and award badges for a user
     */
    public function checkAndAwardBadges(User $user): array
    {
        $newBadges = [];

        try {
            DB::beginTransaction();

            // Get all badges user hasn't earned yet
            $availableBadges = Badge::active()
                ->whereDoesntHave('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('level')
                ->get();

            foreach ($availableBadges as $badge) {
                if ($badge->meetsRequirements($user)) {
                    if ($this->awardBadge($user, $badge)) {
                        $newBadges[] = $badge;
                    }
                }
            }

            DB::commit();

            return $newBadges;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to check and award badges', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Award a badge to a user
     */
    public function awardBadge(User $user, Badge $badge): bool
    {
        try {
            // Attach badge to user
            $user->badges()->attach($badge->id, [
                'earned_at' => now(),
                'is_current' => false,
            ]);

            // Update current badge if this is higher level
            if (!$user->currentBadge || $badge->level > $user->currentBadge->level) {
                $this->setCurrentBadge($user, $badge);
            }

            // Fire badge unlocked event
            event(new BadgeUnlocked($user, $badge));

            Log::info('Badge awarded', [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'badge_name' => $badge->name,
                'level' => $badge->level,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to award badge', [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Set current badge for user
     */
    protected function setCurrentBadge(User $user, Badge $badge): void
    {
        // Mark previous badges as not current
        DB::table('user_badges')
            ->where('user_id', $user->id)
            ->update(['is_current' => false]);

        // Mark this badge as current
        DB::table('user_badges')
            ->where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->update(['is_current' => true]);

        // Update user's current badge reference
        $user->current_badge_id = $badge->id;
        $user->save();
    }

    /**
     * Get all badge progress for user
     */
    public function getAllBadgeProgress(User $user): array
    {
        $badges = Badge::active()->orderBy('level')->get();

        return $badges->map(function ($badge) use ($user) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'level' => $badge->level,
                'icon' => $badge->icon,
                'color' => $badge->color,
                'benefits' => $badge->benefits,
                'requirements' => [
                    'points' => $badge->points_required,
                    'achievements' => $badge->achievements_required,
                ],
                'progress' => $badge->getProgressFor($user),
                'is_current' => $user->current_badge_id === $badge->id,
            ];
        })->toArray();
    }

    /**
     * Get user's badge history
     */
    public function getBadgeHistory(User $user): array
    {
        return $user->badges()
            ->orderByPivot('earned_at', 'desc')
            ->get()
            ->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'level' => $badge->level,
                    'icon' => $badge->icon,
                    'earned_at' => $badge->pivot->earned_at,
                    'is_current' => $badge->pivot->is_current,
                ];
            })
            ->toArray();
    }
}
