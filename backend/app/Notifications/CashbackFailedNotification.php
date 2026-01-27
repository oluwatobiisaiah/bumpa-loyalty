<?php

namespace App\Notifications;

use App\Models\CashbackTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * CashbackFailedNotification
 *
 * Notifies user when cashback payment fails
 */
class CashbackFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public CashbackTransaction $transaction;

    /**
     * Create a new notification instance
     */
    public function __construct(CashbackTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cashback Payment Issue')
            ->greeting("Hello {$notifiable->name},")
            ->line("We encountered an issue processing your cashback payment.")
            ->line("**Amount:** {$this->transaction->currency} {$this->transaction->amount}")
            ->line("**Error:** {$this->transaction->error_message}")
            ->line("Don't worry! We're automatically retrying the payment.")
            ->action('Contact Support', url('/support'))
            ->line('We apologize for any inconvenience.');
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cashback_failed',
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'error_message' => $this->transaction->error_message,
            'will_retry' => true,
        ];
    }
}

