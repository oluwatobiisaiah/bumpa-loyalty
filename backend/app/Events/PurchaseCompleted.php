<?php

namespace App\Events;

use App\Models\Purchase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PurchaseCompleted Event
 *
 * Fired when a purchase is completed
 * Triggers loyalty program processing
 */
class PurchaseCompleted
{
    use Dispatchable, SerializesModels;

    public Purchase $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }
}

