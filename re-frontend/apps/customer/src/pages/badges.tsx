import React from 'react';
import { useLoyaltyDashboard } from '../hooks/use-loyalty-dashboard';
import { BadgeCard, Skeleton, EmptyState, Card, CardContent, CardHeader, CardTitle } from '@loyalty/ui';
import { Award, TrendingUp } from 'lucide-react';

export const BadgesPage: React.FC = () => {
  const { data: dashboard, isLoading } = useLoyaltyDashboard();

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {[...Array(5)].map((_, i) => (
            <Skeleton key={i} className="h-96" />
          ))}
        </div>
      </div>
    );
  }

  if (!dashboard) return null;

  const { current, progress, summary } = dashboard.badges;
  const nextBadge = summary.next_badge;

  return (
    <div className="space-y-8">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Badge Progression</h1>
        <p className="text-muted-foreground mt-2">
          Earn badges by collecting points and unlocking achievements
        </p>
      </div>

      {/* Current Progress */}
      {current && nextBadge && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="h-5 w-5" />
              Your Progress
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-6 md:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground mb-4">Current Badge</p>
                <BadgeCard badge={current} isCurrent />
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-4">Next Badge</p>
                <BadgeCard badge={nextBadge} />
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* All Badges */}
      <div>
        <h2 className="text-2xl font-bold mb-4">All Badges</h2>
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {progress.map((badge) => (
            <BadgeCard
              key={badge.id}
              badge={badge}
              isCurrent={current?.id === badge.id}
            />
          ))}
        </div>
      </div>

      {progress.length === 0 && (
        <EmptyState
          icon={<Award className="h-16 w-16" />}
          title="No badges yet"
          description="Start earning points and unlocking achievements to earn your first badge!"
        />
      )}
    </div>
  );
};