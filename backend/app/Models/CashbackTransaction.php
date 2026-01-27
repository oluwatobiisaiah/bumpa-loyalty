<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CashbackTransaction Model
 *
 * Tracks cashback payments to users
 */
class CashbackTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_id',
        'amount',
        'currency',
        'status',
        'payment_provider',
        'payment_reference',
        'payment_response',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_response' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Transaction statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Payment providers
     */
    public const PROVIDER_PAYSTACK = 'paystack';
    public const PROVIDER_FLUTTERWAVE = 'flutterwave';
    public const PROVIDER_MOCK = 'mock';

    /**
     * The user receiving the cashback
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The related purchase
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(string $reference, array $response = []): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->payment_reference = $reference;
        $this->payment_response = $response;
        $this->processed_at = now();
        $this->save();
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $errorMessage, array $response = []): void
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->payment_response = $response;
        $this->processed_at = now();
        $this->save();
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
