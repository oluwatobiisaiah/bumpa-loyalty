// apps/customer/src/pages/achievements.tsx

import React, { useState } from 'react';
import { useLoyaltyDashboard } from '../hooks/use-loyalty-dashboard';
import { AchievementCard, Skeleton, EmptyState, Badge, Button } from '@loyalty/ui';
import { Trophy, Filter, CheckCircle2, Lock } from 'lucide-react';

type FilterType = 'all' | 'unlocked' | 'locked' | 'in-progress';
type TierFilter = 'all' | 'bronze' | 'silver' | 'gold' | 'platinum';

export const AchievementsPage: React.FC = () => {
    const { data: dashboard, isLoading } = useLoyaltyDashboard();
    const [filter, setFilter] = useState<FilterType>('all');
    const [tierFilter, setTierFilter] = useState<TierFilter>('all');

    if (isLoading) {
        return (
            <div className="space-y-6">
                <Skeleton className="h-12 w-64" />
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {[...Array(6)].map((_, i) => (
                        <Skeleton key={i} className="h-64" />
                    ))}
                </div>
            </div>
        );
    }

    if (!dashboard) return null;

    const achievements = dashboard.achievements.progress;

    const filteredAchievements = achievements.filter((achievement) => {
        // Status filter
        if (filter === 'unlocked' && !achievement.progress.unlocked) return false;
        if (filter === 'locked' && achievement.progress.unlocked) return false;
        if (filter === 'in-progress' && (achievement.progress.unlocked || achievement.progress.current === 0)) return false;

        // Tier filter
        if (tierFilter !== 'all' && achievement.tier !== tierFilter) return false;

        return true;
    });

    const stats = {
        total: achievements.length,
        unlocked: achievements.filter((a) => a.progress.unlocked).length,
        inProgress: achievements.filter((a) => !a.progress.unlocked && a.progress.current > 0).length,
        locked: achievements.filter((a) => !a.progress.unlocked && a.progress.current === 0).length,
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div>
                <h1 className="text-3xl font-bold tracking-tight">Achievements</h1>
                <p className="text-muted-foreground mt-2">
                    Track your progress and unlock rewards
                </p>
            </div>

            {/* Stats */}
            <div className="grid gap-4 md:grid-cols-4">
                <div className="bg-card border rounded-lg p-4">
                    <div className="flex items-center gap-2 text-muted-foreground text-sm mb-1">
                        <Trophy className="h-4 w-4" />
                        <span>Total</span>
                    </div>
                    <p className="text-2xl font-bold">{stats.total}</p>
                </div>
                <div className="bg-card border rounded-lg p-4">
                    <div className="flex items-center gap-2 text-green-600 text-sm mb-1">
                        <CheckCircle2 className="h-4 w-4" />
                        <span>Unlocked</span>
                    </div>
                    <p className="text-2xl font-bold">{stats.unlocked}</p>
                </div>
                <div className="bg-card border rounded-lg p-4">
                    <div className="flex items-center gap-2 text-blue-600 text-sm mb-1">
                        <Filter className="h-4 w-4" />
                        <span>In Progress</span>
                    </div>
                    <p className="text-2xl font-bold">{stats.inProgress}</p>
                </div>
                <div className="bg-card border rounded-lg p-4">
                    <div className="flex items-center gap-2 text-muted-foreground text-sm mb-1">
                        <Lock className="h-4 w-4" />
                        <span>Locked</span>
                    </div>
                    <p className="text-2xl font-bold">{stats.locked}</p>
                </div>
            </div>

            {/* Filters */}
            <div className="flex flex-col sm:flex-row gap-4">
                <div className="flex gap-2 flex-wrap">
                    <Button
                        variant={filter === 'all' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setFilter('all')}
                    >
                        All
                    </Button>
                    <Button
                        variant={filter === 'unlocked' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setFilter('unlocked')}
                    >
                        Unlocked ({stats.unlocked})
                    </Button>
                    <Button
                        variant={filter === 'in-progress' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setFilter('in-progress')}
                    >
                        In Progress ({stats.inProgress})
                    </Button>
                    <Button
                        variant={filter === 'locked' ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setFilter('locked')}
                    >
                        Locked ({stats.locked})
                    </Button>
                </div>

                <div className="flex gap-2 flex-wrap">
                    {['all', 'bronze', 'silver', 'gold', 'platinum'].map((tier) => (
                        <Badge
                            key={tier}
                            variant={tierFilter === tier ? 'default' : 'outline'}
                            className="cursor-pointer capitalize"
                            onClick={() => setTierFilter(tier as TierFilter)}
                        >
                            {tier}
                        </Badge>
                    ))}
                </div>
            </div>

            {/* Achievements Grid */}
            {filteredAchievements.length > 0 ? (
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {filteredAchievements.map((achievement) => (
                        <AchievementCard key={achievement.id} achievement={achievement} />
                    ))}
                </div>
            ) : (
                <EmptyState
                    icon={<Trophy className="h-16 w-16" />}
                    title="No achievements found"
                    description="Try adjusting your filters to see more achievements"
                    action={{
                        label: 'Clear Filters',
                        onClick: () => {
                            setFilter('all');
                            setTierFilter('all');
                        },
                    }}
                />
            )}
        </div>
    );
};