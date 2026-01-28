import React from 'react';
import { cn } from '../lib/utils';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from './ui/card';
import { Progress } from './ui/progress';
import { CheckCircle2 } from 'lucide-react';

interface BadgeCardProps {
  badge: any;
  isCurrent?: boolean;
  className?: string;
}

export const BadgeCard: React.FC<BadgeCardProps> = ({ badge, isCurrent, className }) => {
  const isEarned = badge.progress?.earned || isCurrent;

  return (
    <Card
      className={cn(
        'relative overflow-hidden transition-all',
        isCurrent && 'border-2 border-primary shadow-lg',
        isEarned ? 'hover:shadow-md' : 'opacity-70',
        className
      )}
    >
      {isCurrent && (
        <div className="absolute top-0 right-0 bg-primary text-primary-foreground text-xs px-3 py-1 rounded-bl-lg font-medium">
          Current
        </div>
      )}

      <CardHeader className="text-center">
        <div className="mx-auto mb-4 text-6xl" style={{ color: badge.color }}>
          {badge.icon}
        </div>
        <CardTitle className="text-xl">{badge.name}</CardTitle>
        <CardDescription>{badge.description}</CardDescription>
      </CardHeader>

      <CardContent className="space-y-4">
        <div className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Level {badge.level}</span>
            {!isEarned && badge.progress && (
              <span className="text-xs text-muted-foreground">
                {badge.progress.overall_percentage.toFixed(0)}% complete
              </span>
            )}
          </div>

          {!isEarned && badge.progress && (
            <>
              <div className="space-y-1">
                <div className="flex justify-between text-xs">
                  <span>Points</span>
                  <span>
                    {badge.progress.points.current} / {badge.progress.points.required}
                  </span>
                </div>
                <Progress value={badge.progress.points.percentage} className="h-1.5" />
              </div>

              <div className="space-y-1">
                <div className="flex justify-between text-xs">
                  <span>Achievements</span>
                  <span>
                    {badge.progress.achievements.current} / {badge.progress.achievements.required}
                  </span>
                </div>
                <Progress value={badge.progress.achievements.percentage} className="h-1.5" />
              </div>
            </>
          )}
        </div>

        {badge.benefits && badge.benefits.length > 0 && (
          <div className="space-y-2">
            <p className="text-xs font-medium text-muted-foreground">Benefits:</p>
            <ul className="space-y-1">
              {badge.benefits.map((benefit: string, index: number) => (
                <li key={index} className="text-xs flex items-center gap-2">
                  <CheckCircle2 className="h-3 w-3 text-green-500 flex-shrink-0" />
                  <span>{benefit}</span>
                </li>
              ))}
            </ul>
          </div>
        )}
      </CardContent>
    </Card>
  );
};
