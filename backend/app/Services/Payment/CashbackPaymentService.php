<?php

namespace App\Services\Payment;

use App\Models\User;
use App\Models\Purchase;
use App\Models\CashbackTransaction;
use App\Contracts\PaymentProviderInterface;
use App\Events\CashbackProcessed;
use App\Events\CashbackFailed;
use Illuminate\Support\Facades\Log;

/**
 * CashbackPaymentService
 *
 * Handles cashback payment processing with different providers
 */
class CashbackPaymentService
{
    protected PaymentProviderInterface $provider;

    public function __construct(PaymentProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Calculate cashback amount based on purchase
     */
    public function calculateCashback(Purchase $purchase): float
    {
        $amount = $purchase->amount;

        // Cashback tiers based on purchase amount
        $cashbackRate = match(true) {
            $amount >= 50000 => 0.05, // 5% for purchases >= 50,000
            $amount >= 20000 => 0.03, // 3% for purchases >= 20,000
            $amount >= 5000 => 0.02,  // 2% for purchases >= 5,000
            default => 0.01,          // 1% for all other purchases
        };

        // User badge can add bonus
        $user = $purchase->user;
        $badgeBonus = $this->getBadgeCashbackBonus($user);

        $totalRate = $cashbackRate + $badgeBonus;
        $cashback = $amount * $totalRate;

        return round($cashback, 2);
    }

    /**
     * Get cashback bonus based on user's badge
     */
    protected function getBadgeCashbackBonus(User $user): float
    {
        if (!$user->currentBadge) {
            return 0;
        }

        return match($user->currentBadge->level) {
            5 => 0.02, // Diamond: +2%
            4 => 0.015, // Platinum: +1.5%
            3 => 0.01, // Gold: +1%
            2 => 0.005, // Silver: +0.5%
            default => 0,
        };
    }

    /**
     * Process cashback payment
     */
    public function processCashback(Purchase $purchase): CashbackTransaction
    {
        $user = $purchase->user;
        $amount = $this->calculateCashback($purchase);

        // Create transaction record
        $transaction = CashbackTransaction::create([
            'user_id' => $user->id,
            'purchase_id' => $purchase->id,
            'amount' => $amount,
            'currency' => $purchase->currency ?? 'NGN',
            'status' => CashbackTransaction::STATUS_PENDING,
            'payment_provider' => $this->provider->getName(),
        ]);

        try {
            // Attempt payment through provider
            $transaction->status = CashbackTransaction::STATUS_PROCESSING;
            $transaction->save();

            $result = $this->provider->transferCashback(
                $user,
                $amount,
                $transaction->currency,
                [
                    'transaction_id' => $transaction->id,
                    'purchase_id' => $purchase->id,
                    'description' => "Cashback for purchase #{$purchase->order_id}",
                ]
            );

            if ($result['success']) {
                $transaction->markAsCompleted(
                    $result['reference'],
                    $result['response'] ?? []
                );

                // Update user's total cashback
                $user->addCashback($amount);

                // Fire cashback processed event
                event(new CashbackProcessed($transaction));

                Log::info('Cashback payment successful', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'reference' => $result['reference'],
                ]);
            } else {
                $transaction->markAsFailed(
                    $result['error'] ?? 'Unknown error',
                    $result['response'] ?? []
                );

                // Fire cashback failed event
                event(new CashbackFailed($transaction));

                Log::error('Cashback payment failed', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            $transaction->markAsFailed($e->getMessage());

            // Fire cashback failed event
            event(new CashbackFailed($transaction));

            Log::error('Cashback payment exception', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $transaction->fresh();
    }

    /**
     * Retry failed cashback payment
     */
    public function retryCashback(CashbackTransaction $transaction): bool
    {
        if ($transaction->status !== CashbackTransaction::STATUS_FAILED) {
            return false;
        }

        try {
            $transaction->status = CashbackTransaction::STATUS_PROCESSING;
            $transaction->error_message = null;
            $transaction->save();

            $result = $this->provider->transferCashback(
                $transaction->user,
                $transaction->amount,
                $transaction->currency,
                [
                    'transaction_id' => $transaction->id,
                    'purchase_id' => $transaction->purchase_id,
                    'retry' => true,
                ]
            );

            if ($result['success']) {
                $transaction->markAsCompleted(
                    $result['reference'],
                    $result['response'] ?? []
                );

                $transaction->user->addCashback($transaction->amount);

                // Fire cashback processed event
                event(new CashbackProcessed($transaction));

                return true;
            }

            $transaction->markAsFailed(
                $result['error'] ?? 'Retry failed',
                $result['response'] ?? []
            );

            // Fire cashback failed event
            event(new CashbackFailed($transaction));

            return false;
        } catch (\Exception $e) {
            $transaction->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Get cashback summary for user
     */
    public function getCashbackSummary(User $user): array
    {
        $transactions = $user->cashbackTransactions();

        return [
            'total_earned' => $user->total_cashback,
            'pending' => $transactions->pending()->sum('amount'),
            'completed' => $transactions->completed()->sum('amount'),
            'failed' => $transactions->where('status', CashbackTransaction::STATUS_FAILED)->sum('amount'),
            'transaction_count' => $transactions->count(),
            'recent_transactions' => $transactions
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'amount' => $t->amount,
                    'status' => $t->status,
                    'created_at' => $t->created_at,
                    'processed_at' => $t->processed_at,
                ]),
        ];
    }
}
