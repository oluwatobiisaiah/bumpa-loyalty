<?php

namespace App\Notifications;

use App\Models\Achievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * AchievementUnlockedNotification
 *
 * Notifies user when they unlock an achievement
 * Supports multiple channels: mail, database, broadcast
 */
class AchievementUnlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Achievement $achievement;

    /**
     * Create a new notification instance
     */
    public function __construct(Achievement $achievement)
    {
        $this->achievement = $achievement;
    }

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Achievement Unlocked! 🎉')
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You've unlocked a new achievement: **{$this->achievement->name}**")
            ->line($this->achievement->description)
            ->line("You earned **{$this->achievement->points} points**!")
            ->action('View Your Achievements', url('/dashboard/achievements'))
            ->line('Keep up the great work!');
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'achievement_unlocked',
            'achievement_id' => $this->achievement->id,
            'achievement_name' => $this->achievement->name,
            'achievement_description' => $this->achievement->description,
            'achievement_tier' => $this->achievement->tier,
            'achievement_icon' => $this->achievement->icon,
            'points_earned' => $this->achievement->points,
            'total_points' => $notifiable->total_points,
            'unlocked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'achievement_unlocked',
            'achievement' => [
                'id' => $this->achievement->id,
                'name' => $this->achievement->name,
                'description' => $this->achievement->description,
                'tier' => $this->achievement->tier,
                'icon' => $this->achievement->icon,
                'points' => $this->achievement->points,
            ],
            'message' => "Achievement Unlocked: {$this->achievement->name}!",
            'points_earned' => $this->achievement->points,
            'total_points' => $notifiable->total_points,
        ]);
    }

    /**
     * Get the notification's broadcast channel name
     */
    public function broadcastType(): string
    {
        return 'achievement.unlocked';
    }
}

