import React from 'react';
import { Link } from 'react-router-dom';
import { useLoyaltyDashboard } from '../hooks/use-loyalty-dashboard';
import {
  StatsCard,
  AchievementCard,
  BadgeCard,
  Button,
  Skeleton,
  EmptyState,
} from '@loyalty/ui';
import { Trophy, Award, Wallet, TrendingUp, ArrowRight, Zap } from 'lucide-react';

export const DashboardPage: React.FC = () => {
  const { data: dashboard, isLoading, error } = useLoyaltyDashboard();

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {[...Array(4)].map((_, i) => (
            <Skeleton key={i} className="h-32" />
          ))}
        </div>
        <Skeleton className="h-96" />
      </div>
    );
  }

  if (error) {
    return (
      <EmptyState
        icon={<Zap className="h-16 w-16" />}
        title="Oops! Something went wrong"
        description="We couldn't load your dashboard. Please try refreshing the page."
        action={{
          label: 'Refresh',
          onClick: () => window.location.reload(),
        }}
      />
    );
  }

  if (!dashboard) return null;

  const recentAchievements = dashboard.achievements.recently_unlocked.slice(0, 3);
  const nextBadge = dashboard.badges.summary.next_badge;

  return (
    <div className="space-y-8">
      {/* Welcome Header */}
      <div className="space-y-2">
        <h1 className="text-3xl font-bold tracking-tight">
          Welcome back, {dashboard.user.name}! 👋
        </h1>
        <p className="text-muted-foreground">
          Here's what's happening with your loyalty rewards
        </p>
      </div>

      {/* Stats Overview */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatsCard
          title="Total Points"
          value={dashboard.user.total_points.toLocaleString()}
          description="Lifetime points earned"
          icon={<Trophy className="h-5 w-5" />}
        />
        <StatsCard
          title="Total Cashback"
          value={`₦${dashboard.user.total_cashback.toLocaleString()}`}
          description="Total earnings"
          icon={<Wallet className="h-5 w-5" />}
        />
        <StatsCard
          title="Achievements"
          value={`${dashboard.achievements.summary.unlocked_achievements}/${dashboard.achievements.summary.total_achievements}`}
          description={`${dashboard.achievements.summary.completion_percentage}% complete`}
          icon={<Award className="h-5 w-5" />}
        />
        <StatsCard
          title="Current Badge"
          value={dashboard.badges.current?.name || 'None'}
          description={dashboard.badges.current ? `Level ${dashboard.badges.current.level}` : 'Start earning!'}
          icon={<TrendingUp className="h-5 w-5" />}
        />
      </div>

      {/* Current Badge Progress */}
      {nextBadge && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-2xl font-bold">Next Badge</h2>
            <Link to="/badges">
              <Button variant="ghost" size="sm">
                View All
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </Link>
          </div>
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {dashboard.badges.current && (
              <BadgeCard badge={dashboard.badges.current} isCurrent />
            )}
            <BadgeCard badge={nextBadge} />
          </div>
        </div>
      )}

      {/* Recent Achievements */}
      {recentAchievements.length > 0 && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-2xl font-bold">Recently Unlocked 🎉</h2>
            <Link to="/achievements">
              <Button variant="ghost" size="sm">
                View All
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </Link>
          </div>
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {recentAchievements.map((achievement) => (
              <AchievementCard
                key={achievement.id}
                achievement={{
                  ...achievement,
                  progress: {
                    current: achievement.criteria.target,
                    required: achievement.criteria.target,
                    percentage: 100,
                    unlocked: true,
                  },
                }}
              />
            ))}
          </div>
        </div>
      )}

      {/* Achievements in Progress */}
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold">In Progress</h2>
          <Link to="/achievements">
            <Button variant="ghost" size="sm">
              View All
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </Link>
        </div>
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {dashboard.achievements.progress
            .filter((a) => !a.progress.unlocked && a.progress.current > 0)
            .slice(0, 3)
            .map((achievement) => (
              <AchievementCard key={achievement.id} achievement={achievement} />
            ))}
        </div>
        {dashboard.achievements.progress.filter((a) => !a.progress.unlocked && a.progress.current > 0).length === 0 && (
          <EmptyState
            icon={<Trophy className="h-12 w-12" />}
            title="No achievements in progress"
            description="Start making purchases to unlock achievements!"
            action={{
              label: 'View All Achievements',
              onClick: () => (window.location.href = '/achievements'),
            }}
          />
        )}
      </div>

      {/* Cashback Summary */}
      <div className="space-y-4">
        <h2 className="text-2xl font-bold">Cashback Summary</h2>
        <div className="grid gap-4 md:grid-cols-3">
          <StatsCard
            title="Total Earned"
            value={`₦${dashboard.cashback.total_earned.toLocaleString()}`}
            description="All-time earnings"
          />
          <StatsCard
            title="Pending"
            value={`₦${dashboard.cashback.pending.toLocaleString()}`}
            description="Being processed"
          />
          <StatsCard
            title="Success Rate"
            value={`${((dashboard.cashback.completed / dashboard.cashback.transaction_count) * 100 || 0).toFixed(1)}%`}
            description={`${dashboard.cashback.transaction_count} transactions`}
          />
        </div>
      </div>
    </div>
  );
};