<?php
namespace App\Listeners;

use App\Events\CashbackProcessed;
use App\Notifications\CashbackProcessedNotification;
use Illuminate\Support\Facades\Log;

/**
 * NotifyUserOfCashback Listener
 */
class NotifyUserOfCashback
{
    public function handle(CashbackProcessed $event): void
    {
        try {
            $event->transaction->user->notify(
                new CashbackProcessedNotification($event->transaction)
            );

            Log::info('Cashback notification sent', [
                'user_id' => $event->transaction->user_id,
                'transaction_id' => $event->transaction->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send cashback notification', [
                'transaction_id' => $event->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
