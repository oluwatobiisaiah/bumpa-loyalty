<?php


namespace App\Listeners;

use App\Events\PurchaseCompleted;
use App\Jobs\ProcessLoyaltyRewards;
use Illuminate\Support\Facades\Log;

/**
 * ProcessPurchaseForLoyalty Listener
 *
 * Listens for purchase completion and dispatches loyalty processing job
 */
class ProcessPurchaseForLoyalty
{
    /**
     * Handle the event
     */
    public function handle(PurchaseCompleted $event): void
    {
        Log::info('Purchase completed event received', [
            'purchase_id' => $event->purchase->id,
            'user_id' => $event->purchase->user_id,
        ]);

        // Dispatch to queue for async processing
        ProcessLoyaltyRewards::dispatch($event->purchase);
    }
}
