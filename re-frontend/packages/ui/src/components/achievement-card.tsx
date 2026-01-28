import React from 'react';
import { cn } from '../lib/utils';
import type { AchievementProgress } from '@loyalty/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Progress } from './ui/progress';
import { Badge } from './ui/badge';
import { CheckCircle2, Lock } from 'lucide-react';

interface AchievementCardProps {
  achievement: AchievementProgress;
  className?: string;
}

export const AchievementCard: React.FC<AchievementCardProps> = ({ achievement, className }) => {
  const isUnlocked = achievement.progress.unlocked;

  const tierColors = {
    bronze: 'from-amber-600 to-amber-800',
    silver: 'from-gray-400 to-gray-600',
    gold: 'from-yellow-400 to-yellow-600',
    platinum: 'from-purple-400 to-purple-600',
  };

  return (
    <Card
      className={cn(
        'relative overflow-hidden transition-all hover:shadow-lg',
        isUnlocked ? 'border-green-500/50' : 'opacity-75',
        className
      )}
    >
      {/* Background gradient for unlocked */}
      {isUnlocked && (
        <div className="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent" />
      )}

      <CardHeader className="relative">
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-3">
            <div
              className={cn(
                'text-4xl p-3 rounded-lg bg-gradient-to-br',
                tierColors[achievement.tier]
              )}
            >
              {achievement.icon}
            </div>
            <div>
              <CardTitle className="text-lg flex items-center gap-2">
                {achievement.name}
                {isUnlocked && <CheckCircle2 className="h-5 w-5 text-green-500" />}
              </CardTitle>
              <CardDescription className="mt-1">{achievement.description}</CardDescription>
            </div>
          </div>
          <Badge variant={isUnlocked ? 'default' : 'secondary'} className="ml-2">
            {achievement.tier}
          </Badge>
        </div>
      </CardHeader>

      <CardContent className="relative space-y-4">
        {!isUnlocked ? (
          <>
            <div className="space-y-2">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Progress</span>
                <span className="font-medium">
                  {achievement.progress.current} / {achievement.progress.required}
                </span>
              </div>
              <Progress value={achievement.progress.percentage} className="h-2" />
            </div>
            <div className="flex items-center justify-between text-sm">
              <div className="flex items-center gap-2 text-muted-foreground">
                <Lock className="h-4 w-4" />
                <span>Locked</span>
              </div>
              <span className="font-semibold text-primary">{achievement.points} points</span>
            </div>
          </>
        ) : (
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-green-600">Unlocked!</span>
              <span className="text-sm font-semibold text-primary">+{achievement.points} points</span>
            </div>
            {achievement.progress.unlocked_at && (
              <p className="text-xs text-muted-foreground">
                {new Date(achievement.progress.unlocked_at).toLocaleDateString()}
              </p>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
};