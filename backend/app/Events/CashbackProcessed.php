<?php
namespace App\Events;

use App\Models\CashbackTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CashbackProcessed Event
 *
 * Fired when cashback is successfully processed
 */
class CashbackProcessed
{
    use Dispatchable, SerializesModels;

    public CashbackTransaction $transaction;

    public function __construct(CashbackTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}

