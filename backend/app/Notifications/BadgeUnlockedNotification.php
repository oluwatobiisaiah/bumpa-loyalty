<?php
namespace App\Notifications;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * BadgeUnlockedNotification
 *
 * Notifies user when they earn a new badge
 * Supports multiple channels: mail, database, broadcast
 */
class BadgeUnlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Badge $badge;


    public function __construct(Badge $badge)
    {
        $this->badge = $badge;
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
        $benefitsList = is_array($this->badge->benefits)
            ? implode(', ', $this->badge->benefits)
            : 'Exclusive benefits';

        return (new MailMessage)
            ->subject('New Badge Earned! 🏆')
            ->greeting("Amazing, {$notifiable->name}!")
            ->line("You've earned a new badge: **{$this->badge->name}** {$this->badge->icon}")
            ->line($this->badge->description)
            ->line("**Level:** {$this->badge->level}")
            ->line("**Benefits:** {$benefitsList}")
            ->action('View Your Profile', url('/dashboard/profile'))
            ->line("You're on fire! Keep going!");
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'badge_unlocked',
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->name,
            'badge_description' => $this->badge->description,
            'badge_level' => $this->badge->level,
            'badge_icon' => $this->badge->icon,
            'badge_color' => $this->badge->color,
            'benefits' => $this->badge->benefits,
            'earned_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'badge_unlocked',
            'badge' => [
                'id' => $this->badge->id,
                'name' => $this->badge->name,
                'description' => $this->badge->description,
                'level' => $this->badge->level,
                'icon' => $this->badge->icon,
                'color' => $this->badge->color,
                'benefits' => $this->badge->benefits,
            ],
            'message' => "Badge Earned: {$this->badge->name}!",
            'celebration' => true,
        ]);
    }

    /**
     * Get the notification's broadcast channel name
     */
    public function broadcastType(): string
    {
        return 'badge.unlocked';
    }
}
