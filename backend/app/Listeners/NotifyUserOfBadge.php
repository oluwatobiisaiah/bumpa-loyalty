<?php
namespace App\Listeners;

use App\Events\BadgeUnlocked;
use App\Notifications\BadgeUnlockedNotification;
use Illuminate\Support\Facades\Log;

/**
 * NotifyUserOfBadge Listener
 *
 * Sends notification when badge is earned
 */
class NotifyUserOfBadge
{
    public function handle(BadgeUnlocked $event): void
    {
        try {
            $event->user->notify(
                new BadgeUnlockedNotification($event->badge)
            );

            Log::info('Badge notification sent', [
                'user_id' => $event->user->id,
                'badge_id' => $event->badge->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send badge notification', [
                'user_id' => $event->user->id,
                'badge_id' => $event->badge->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
    