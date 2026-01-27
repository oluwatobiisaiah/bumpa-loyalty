<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Purchase Model
 *
 * Represents a user purchase that triggers loyalty events
 */
class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'items',
        'metadata',
        'processed_for_loyalty',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'items' => 'array',
        'metadata' => 'array',
        'processed_for_loyalty' => 'boolean',
    ];

    /**
     * Purchase statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * The user who made the purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark as processed for loyalty
     */
    public function markAsProcessed(): void
    {
        $this->processed_for_loyalty = true;
        $this->save();
    }

    /**
     * Check if purchase is eligible for loyalty rewards
     */
    public function isEligibleForLoyalty(): bool
    {
        return $this->status === self::STATUS_COMPLETED &&
               !$this->processed_for_loyalty;
    }

    /**
     * Scope for completed purchases
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for unprocessed purchases
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed_for_loyalty', false);
    }
}

