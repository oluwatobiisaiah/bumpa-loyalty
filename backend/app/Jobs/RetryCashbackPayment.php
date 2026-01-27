<?php
namespace App\Jobs;

use App\Models\CashbackTransaction;
use App\Services\Payment\CashbackPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * RetryCashbackPayment Job
 *
 * Retries failed cashback payments
 */
class RetryCashbackPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CashbackTransaction $transaction;
    public int $tries = 5;
    public int $timeout = 60;
    public int $backoff = 300; // 5 minutes between retries

    public function __construct(CashbackTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function handle(CashbackPaymentService $cashbackService): void
    {
        try {
            Log::info('Retrying cashback payment', [
                'transaction_id' => $this->transaction->id,
                'attempt' => $this->attempts(),
            ]);

            $success = $cashbackService->retryCashback($this->transaction);

            if ($success) {
                Log::info('Cashback retry successful', [
                    'transaction_id' => $this->transaction->id,
                ]);
            } else {
                Log::warning('Cashback retry failed', [
                    'transaction_id' => $this->transaction->id,
                ]);

                // Will be retried automatically if attempts < tries
                throw new \Exception('Cashback retry failed');
            }
        } catch (\Exception $e) {
            Log::error('Cashback retry error', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('RetryCashbackPayment job failed permanently', [
            'transaction_id' => $this->transaction->id,
            'error' => $exception->getMessage(),
        ]);

        // Could send notification to admin and customer
    }
}

