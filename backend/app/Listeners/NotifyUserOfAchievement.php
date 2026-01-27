<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Notifications\AchievementUnlockedNotification;
use Illuminate\Support\Facades\Log;

/**
 * NotifyUserOfAchievement Listener
 *
 * Sends notification when achievement is unlocked
 */
class NotifyUserOfAchievement
{
    public function handle(AchievementUnlocked $event): void
    {
        try {
            $event->user->notify(
                new AchievementUnlockedNotification($event->achievement)
            );

            Log::info('Achievement notification sent', [
                'user_id' => $event->user->id,
                'achievement_id' => $event->achievement->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send achievement notification', [
                'user_id' => $event->user->id,
                'achievement_id' => $event->achievement->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

