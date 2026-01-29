<?php

namespace App\Jobs;

use App\Models\Purchase;
use App\Services\Loyalty\AchievementService;
use App\Services\Loyalty\BadgeService;
use App\Services\Payment\CashbackPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessLoyaltyRewards Job
 *
 * Processes achievements, badges, and cashback for a purchase
 * This is the main event-driven job triggered by purchase completion
 */
class ProcessLoyaltyRewards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Purchase $purchase;
    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Execute the job
     */
    public function handle(
        AchievementService $achievementService,
        BadgeService $badgeService,
        CashbackPaymentService $cashbackService
    ): void {
        Log::info('Starting ProcessLoyaltyRewards job', [
            'purchase_id' => $this->purchase->id,
            'job_id' => $this->job ? $this->job->getJobId() : null,
        ]);

        try {
            Log::info('Processing loyalty rewards - initial checks', [
                'purchase_id' => $this->purchase->id,
                'user_id' => $this->purchase->user_id,
                'amount' => $this->purchase->amount,
                'status' => $this->purchase->status,
                'processed_for_loyalty' => $this->purchase->processed_for_loyalty,
            ]);

            // Skip if already processed
            if ($this->purchase->processed_for_loyalty) {
                Log::info('Purchase already processed for loyalty', [
                    'purchase_id' => $this->purchase->id,
                ]);
                return;
            }

            // Only process completed purchases
            if (!$this->purchase->isEligibleForLoyalty()) {
                Log::info('Purchase not eligible for loyalty', [
                    'purchase_id' => $this->purchase->id,
                    'status' => $this->purchase->status,
                ]);
                return;
            }

            Log::info('Loading user relationship', [
                'purchase_id' => $this->purchase->id,
            ]);
            $user = $this->purchase->user;

            if (!$user) {
                Log::error('User not found for purchase', [
                    'purchase_id' => $this->purchase->id,
                    'user_id' => $this->purchase->user_id,
                ]);
                throw new \Exception('User not found for purchase');
            }

            Log::info('User loaded successfully', [
                'purchase_id' => $this->purchase->id,
                'user_id' => $user->id,
            ]);

            // Step 1: Process achievements
            Log::info('Starting achievement processing', [
                'purchase_id' => $this->purchase->id,
            ]);
            $unlockedAchievements = $achievementService->processPurchaseForAchievements($this->purchase);

            Log::info('Achievements processed', [
                'purchase_id' => $this->purchase->id,
                'unlocked_count' => count($unlockedAchievements),
            ]);

            // Step 2: Check and award badges
            Log::info('Starting badge processing', [
                'purchase_id' => $this->purchase->id,
                'user_id' => $user->id,
            ]);
            $newBadges = $badgeService->checkAndAwardBadges($user);

            Log::info('Badges processed', [
                'purchase_id' => $this->purchase->id,
                'new_badges' => count($newBadges),
            ]);

            // Step 3: Process cashback payment
            Log::info('Starting cashback processing', [
                'purchase_id' => $this->purchase->id,
            ]);
            $cashbackTransaction = $cashbackService->processCashback($this->purchase);

            Log::info('Cashback processed', [
                'purchase_id' => $this->purchase->id,
                'transaction_id' => $cashbackTransaction->id,
                'amount' => $cashbackTransaction->amount,
                'status' => $cashbackTransaction->status,
            ]);

            // Mark purchase as processed
            Log::info('Marking purchase as processed', [
                'purchase_id' => $this->purchase->id,
            ]);
            $this->purchase->markAsProcessed();

            Log::info('Loyalty rewards processing completed', [
                'purchase_id' => $this->purchase->id,
                'achievements_unlocked' => count($unlockedAchievements),
                'badges_earned' => count($newBadges),
                'cashback_amount' => $cashbackTransaction->amount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to process loyalty rewards', [
                'purchase_id' => $this->purchase->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessLoyaltyRewards job failed permanently', [
            'purchase_id' => $this->purchase->id,
            'error' => $exception->getMessage(),
        ]);

        // Could send notification to admin here
    }
}

