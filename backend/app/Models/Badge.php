<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Badge Model
 *
 * Represents tier-based badges that users earn based on
 * total points or achievement count.
 */
class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'level',
        'points_required',
        'achievements_required',
        'icon',
        'color',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'achievements_required' => 'integer',
        'benefits' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Badge levels enumeration
     */
    public const LEVEL_BRONZE = 1;
    public const LEVEL_SILVER = 2;
    public const LEVEL_GOLD = 3;
    public const LEVEL_PLATINUM = 4;
    public const LEVEL_DIAMOND = 5;

    /**
     * Users who have earned this badge
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot(['earned_at', 'is_current'])
            ->withTimestamps();
    }

    /**
     * Check if a user has earned this badge
     */
    public function isEarnedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user meets requirements for this badge
     */
    public function meetsRequirements(User $user): bool
    {
        $totalPoints = $user->achievements()
            ->sum('achievements.points');

        $achievementCount = $user->achievements()->count();

        return ($totalPoints >= $this->points_required) &&
               ($achievementCount >= $this->achievements_required);
    }

    /**
     * Get progress towards this badge for a user
     */
    public function getProgressFor(User $user): array
    {
        $totalPoints = $user->achievements()->sum('achievements.points');
        $achievementCount = $user->achievements()->count();

        return [
            'points' => [
                'current' => $totalPoints,
                'required' => $this->points_required,
                'percentage' => min(100, $this->points_required ?($totalPoints / $this->points_required):0 * 100),
            ],
            'achievements' => [
                'current' => $achievementCount,
                'required' => $this->achievements_required,
                'percentage' => min(100, $this->achievements_required ?($achievementCount / $this->achievements_required):0 * 100),
            ],
            'overall_percentage' => min(100, (
                ($this->points_required?($totalPoints / $this->points_required):0 * 50) +
                ($this->achievements_required?($achievementCount / $this->achievements_required):0 * 50)
            )),
            'earned' => $this->isEarnedBy($user),
        ];
    }

    /**
     * Scope for active badges only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by level
     */
    public function scopeOrderedByLevel($query)
    {
        return $query->orderBy('level');
    }

    /**
     * Get next badge for a user
     */
    public static function getNextBadgeFor(User $user): ?self
    {
        $currentBadge = $user->currentBadge;
        $currentLevel = $currentBadge ? $currentBadge->level : 0;

        return self::active()
            ->where('level', '>', $currentLevel)
            ->orderBy('level')
            ->first();
    }
}
