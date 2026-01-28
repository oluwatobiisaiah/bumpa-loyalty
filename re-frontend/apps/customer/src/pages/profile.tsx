import React from 'react';
import { useAuthStore } from '../store/auth-store';
import { useLoyaltyDashboard } from '../hooks/use-loyalty-dashboard';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  Avatar,
  AvatarFallback,
  Badge,
  Skeleton,
} from '@loyalty/ui';
import { User, Mail, Calendar, Trophy, Award, Wallet } from 'lucide-react';
import { getInitials, formatCurrency, formatDate } from '@loyalty/ui/lib/utils';

export const ProfilePage: React.FC = () => {
  const { user } = useAuthStore();
  const { data: dashboard, isLoading } = useLoyaltyDashboard();

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  if (!user || !dashboard) return null;

  return (
    <div className="space-y-6 max-w-4xl">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Profile</h1>
        <p className="text-muted-foreground mt-2">Manage your account and loyalty information</p>
      </div>

      {/* Profile Card */}
      <Card>
        <CardHeader>
          <CardTitle>Personal Information</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-start gap-6">
            <Avatar className="h-20 w-20">
              <AvatarFallback className="text-2xl">
                {getInitials(user.name)}
              </AvatarFallback>
            </Avatar>

            <div className="flex-1 space-y-4">
              <div className="flex items-center gap-3">
                <User className="h-5 w-5 text-muted-foreground" />
                <div>
                  <p className="text-sm text-muted-foreground">Name</p>
                  <p className="font-medium">{user.name}</p>
                </div>
              </div>

              <div className="flex items-center gap-3">
                <Mail className="h-5 w-5 text-muted-foreground" />
                <div>
                  <p className="text-sm text-muted-foreground">Email</p>
                  <p className="font-medium">{user.email}</p>
                </div>
              </div>

              <div className="flex items-center gap-3">
                <Calendar className="h-5 w-5 text-muted-foreground" />
                <div>
                  <p className="text-sm text-muted-foreground">Member Since</p>
                  <p className="font-medium">{formatDate(user.created_at || new Date().toISOString())}</p>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Loyalty Stats */}
      <Card>
        <CardHeader>
          <CardTitle>Loyalty Stats</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-6 md:grid-cols-3">
            <div className="flex items-start gap-3">
              <div className="bg-primary/10 p-2 rounded-lg">
                <Trophy className="h-6 w-6 text-primary" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Total Points</p>
                <p className="text-2xl font-bold">{user.total_points.toLocaleString()}</p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <div className="bg-green-500/10 p-2 rounded-lg">
                <Wallet className="h-6 w-6 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Total Cashback</p>
                <p className="text-2xl font-bold">{formatCurrency(user.total_cashback)}</p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <div className="bg-blue-500/10 p-2 rounded-lg">
                <Award className="h-6 w-6 text-blue-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Current Badge</p>
                <div className="flex items-center gap-2 mt-1">
                  {dashboard.badges.current ? (
                    <Badge variant="default">
                      {dashboard.badges.current.icon} {dashboard.badges.current.name}
                    </Badge>
                  ) : (
                    <Badge variant="outline">No badge yet</Badge>
                  )}
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Achievement Summary */}
      <Card>
        <CardHeader>
          <CardTitle>Achievement Progress</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex justify-between items-center">
              <span className="text-sm text-muted-foreground">Achievements Unlocked</span>
              <span className="font-semibold">
                {dashboard.achievements.summary.unlocked_achievements} / {dashboard.achievements.summary.total_achievements}
              </span>
            </div>
            <div className="w-full bg-secondary rounded-full h-2">
              <div
                className="bg-primary h-2 rounded-full transition-all"
                style={{ width: `${dashboard.achievements.summary.completion_percentage}%` }}
              />
            </div>
            <p className="text-sm text-center text-muted-foreground">
              {dashboard.achievements.summary.completion_percentage}% Complete
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};