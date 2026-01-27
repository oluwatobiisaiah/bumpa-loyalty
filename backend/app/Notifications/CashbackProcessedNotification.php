<?php
namespace App\Notifications;

use App\Models\CashbackTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * CashbackProcessedNotification
 *
 * Notifies user when cashback is successfully processed
 */
class CashbackProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public CashbackTransaction $transaction;


    public function __construct(CashbackTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }


    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cashback Received! 💰')
            ->greeting("Great news, {$notifiable->name}!")
            ->line("Your cashback of **{$this->transaction->currency} {$this->transaction->amount}** has been processed successfully!")
            ->line("**Transaction Reference:** {$this->transaction->payment_reference}")
            ->line("**Processed:** {$this->transaction->processed_at->format('M d, Y h:i A')}")
            ->action('View Transaction History', url('/dashboard/cashback'))
            ->line('Thank you for being a loyal customer!');
    }


    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cashback_processed',
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'payment_reference' => $this->transaction->payment_reference,
            'processed_at' => $this->transaction->processed_at->toIso8601String(),
            'total_cashback' => $notifiable->total_cashback,
        ];
    }
}
