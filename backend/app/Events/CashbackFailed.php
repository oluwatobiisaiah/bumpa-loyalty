<?php
namespace App\Events;

use App\Models\CashbackTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * CashbackFailed Event
 *
 * Fired when cashback payment fails
 */
class CashbackFailed
{
    use Dispatchable, SerializesModels;

    public CashbackTransaction $transaction;

    public function __construct(CashbackTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
