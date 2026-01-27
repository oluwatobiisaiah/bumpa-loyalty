<?php
namespace App\Listeners;

use App\Events\CashbackFailed;
use App\Notifications\CashbackFailedNotification;
use Illuminate\Support\Facades\Log;

/**
 * NotifyUserOfCashbackFailure Listener
 */
class NotifyUserOfCashbackFailure
{
    public function handle(CashbackFailed $event): void
    {
        try {
            $event->transaction->user->notify(
                new CashbackFailedNotification($event->transaction)
            );

            Log::info('Cashback failure notification sent', [
                'user_id' => $event->transaction->user_id,
                'transaction_id' => $event->transaction->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send cashback failure notification', [
                'transaction_id' => $event->transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

