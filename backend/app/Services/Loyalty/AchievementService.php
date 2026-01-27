<?php

namespace App\Services\Loyalty;

use App\Models\User;
use App\Models\Achievement;
use App\Models\Purchase;
use App\Events\AchievementUnlocked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * AchievementService
 *
 * Handles all business logic related to achievements including
 * progress tracking, unlocking, and validation.
 */
class AchievementService
{
    /**
     * Process purchase for achievements
     */
    public function processPurchaseForAchievements(Purchase $purchase): array
    {
        $user = $purchase->user;
        $unlockedAchievements = [];

        try {
            DB::beginTransaction();

            // Get all active achievements that user hasn't unlocked yet
            $achievements = Achievement::active()
                ->whereDoesntHave('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            foreach ($achievements as $achievement) {
                if ($this->checkAndUpdateProgress($user, $achievement, $purchase)) {
                    $unlockedAchievements[] = $achievement;
                }
            }

            DB::commit();

            return $unlockedAchievements;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process achievements for purchase', [
                'purchase_id' => $purchase->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check and update achievement progress
     */
    protected function checkAndUpdateProgress(
        User $user,
        Achievement $achievement,
        Purchase $purchase
    ): bool {
        $criteria = $achievement->criteria;
        $type = $achievement->type;

        $currentProgress = $this->getCurrentProgress($user, $achievement, $type);
        $newProgress = $this->calculateNewProgress($currentProgress, $purchase, $criteria, $type);

        // Update or create progress entry
        $user->achievements()->syncWithoutDetaching([
            $achievement->id => [
                'progress' => $newProgress,
                'unlocked_at' => null,
                'metadata' => json_encode([
                    'last_updated' => now()->toDateTimeString(),
                    'purchase_id' => $purchase->id,
                ]),
            ],
        ]);

        // Check if achievement is unlocked
        $target = $criteria['target'] ?? 1;
        if ($newProgress >= $target) {
            return $this->unlockAchievement($user, $achievement);
        }

        return false;
    }

    /**
     * Get current progress for an achievement
     */
    protected function getCurrentProgress(User $user, Achievement $achievement, string $type): int|float
    {
        $existingProgress = DB::table('user_achievements')
            ->where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->value('progress');

        if ($existingProgress !== null) {
            return $existingProgress;
        }

        // Calculate from historical data
        return match($type) {
            Achievement::TYPE_PURCHASE => $user->purchases()->completed()->count(),
            Achievement::TYPE_SPENDING => $user->purchases()->completed()->sum('amount'),
            Achievement::TYPE_REVIEW => 0, // Would integrate with review system
            Achievement::TYPE_REFERRAL => 0, // Would integrate with referral system
            Achievement::TYPE_STREAK => 0, // Requires separate streak tracking
            default => 0,
        };
    }

    /**
     * Calculate new progress based on purchase
     */
    protected function calculateNewProgress(
        int|float $currentProgress,
        Purchase $purchase,
        array $criteria,
        string $type
    ): int|float {
        return match($type) {
            Achievement::TYPE_PURCHASE => $currentProgress + 1,
            Achievement::TYPE_SPENDING => $currentProgress + $purchase->amount,
            default => $currentProgress,
        };
    }

    /**
     * Unlock an achievement for a user
     */
    public function unlockAchievement(User $user, Achievement $achievement): bool
    {
        try {
            // Update the pivot record with unlock timestamp
            $user->achievements()->updateExistingPivot($achievement->id, [
                'unlocked_at' => now(),
                'metadata' => json_encode([
                    'unlocked_at' => now()->toDateTimeString(),
                ]),
            ]);

            // Update user's total points
            $user->updateTotalPoints();

            // Fire achievement unlocked event
            event(new AchievementUnlocked($user, $achievement));

            Log::info('Achievement unlocked', [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'achievement_name' => $achievement->name,
                'points' => $achievement->points,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to unlock achievement', [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get user's achievement progress
     */
    public function getUserAchievementProgress(User $user): Collection
    {
        $allAchievements = Achievement::active()->get();

        return $allAchievements->map(function ($achievement) use ($user) {
            $progress = $achievement->getProgressFor($user);

            return [
                'id' => $achievement->id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'type' => $achievement->type,
                'tier' => $achievement->tier,
                'points' => $achievement->points,
                'icon' => $achievement->icon,
                'progress' => $progress,
            ];
        });
    }

    /**
     * Get recently unlocked achievements
     */
    public function getRecentlyUnlocked(User $user, int $limit = 5): Collection
    {
        return $user->achievements()
            ->wherePivot('unlocked_at', '!=', null)
            ->orderByPivot('unlocked_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
