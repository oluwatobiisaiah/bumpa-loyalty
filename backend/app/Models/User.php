<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Model
 *
 * Extended with loyalty program relationships and methods
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'total_points',
        'total_cashback',
        'current_badge_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'total_points' => 'integer',
        'total_cashback' => 'decimal:2',
    ];

    /**
     * User roles
     */
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_ADMIN = 'admin';

    /**
     * Achievements unlocked by the user
     */
    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot(['unlocked_at', 'progress', 'metadata'])
            ->withTimestamps()
            ->orderByPivot('unlocked_at', 'desc');
    }

    /**
     * Badges earned by the user
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['earned_at', 'is_current'])
            ->withTimestamps()
            ->orderByPivot('earned_at', 'desc');
    }

    /**
     * Current active badge
     */
    public function currentBadge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'current_badge_id');
    }

    /**
     * User's purchases
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * User's cashback transactions
     */
    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CashbackTransaction::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Get total achievement points
     */
    public function getTotalAchievementPoints(): int
    {
        return $this->achievements()->sum('achievements.points');
    }

    /**
     * Update total points and recalculate badge
     */
    public function updateTotalPoints(): void
    {
        $this->total_points = $this->getTotalAchievementPoints();
        $this->save();
    }

    /**
     * Add cashback amount
     */
    public function addCashback(float $amount): void
    {
        $this->total_cashback += $amount;
        $this->save();
    }

    /**
     * Get achievement progress summary
     */
    public function getAchievementProgressSummary(): array
    {
        $allAchievements = Achievement::active()->get();
        $unlockedCount = $this->achievements()->count();

        return [
            'total_achievements' => $allAchievements->count(),
            'unlocked_achievements' => $unlockedCount,
            'locked_achievements' => $allAchievements->count() - $unlockedCount,
            'completion_percentage' => $allAchievements->count() > 0
                ? round(($unlockedCount / $allAchievements->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get badge progress summary
     */
    public function getBadgeProgressSummary(): array
    {
        $currentBadge = $this->currentBadge;
        $nextBadge = Badge::getNextBadgeFor($this);

        return [
            'current_badge' => $currentBadge ? [
                'id' => $currentBadge->id,
                'name' => $currentBadge->name,
                'level' => $currentBadge->level,
                'icon' => $currentBadge->icon,
            ] : null,
            'next_badge' => $nextBadge ? [
                'id' => $nextBadge->id,
                'name' => $nextBadge->name,
                'level' => $nextBadge->level,
                'progress' => $nextBadge->getProgressFor($this),
            ] : null,
            'total_badges_earned' => $this->badges()->count(),
        ];
    }
}
