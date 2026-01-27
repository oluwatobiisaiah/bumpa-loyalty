<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Achievement Model
 *
 * Represents individual achievements that users can unlock
 * through various actions in the loyalty program.
 */
class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'criteria',
        'points',
        'icon',
        'tier',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    /**
     * Achievement types enumeration
     */
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_SPENDING = 'spending';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_REVIEW = 'review';
    public const TYPE_STREAK = 'streak';

    /**
     * Achievement tiers
     */
    public const TIER_BRONZE = 'bronze';
    public const TIER_SILVER = 'silver';
    public const TIER_GOLD = 'gold';
    public const TIER_PLATINUM = 'platinum';

    /**
     * Users who have unlocked this achievement
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot(['unlocked_at', 'progress', 'metadata'])
            ->withTimestamps();
    }

    /**
     * Check if a user has unlocked this achievement
     */
    public function isUnlockedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get progress for a specific user
     */
    public function getProgressFor(User $user): array
    {
        $userAchievement = $this->users()
            ->where('user_id', $user->id)
            ->first();

        if (!$userAchievement) {
            return [
                'current' => 0,
                'required' => $this->criteria['target'] ?? 1,
                'percentage' => 0,
                'unlocked' => false,
            ];
        }

        $current = $userAchievement->pivot->progress ?? 0;
        $required = $this->criteria['target'] ?? 1;

        return [
            'current' => $current,
            'required' => $required,
            'percentage' => min(100, ($current / $required) * 100),
            'unlocked' => true,
            'unlocked_at' => $userAchievement->pivot->unlocked_at,
        ];
    }

    /**
     * Scope for active achievements only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by tier
     */
    public function scopeOfTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }
}
