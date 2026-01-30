import React from 'react';
import { useAdminStatistics } from '../hooks/use-admin-statistics';
import {
  StatsCard,
  Skeleton,
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  Badge,
  Avatar,
  AvatarFallback,
} from '@loyalty/ui';
import {
  Users,
  Trophy,
  Award,
  Wallet,
  TrendingUp,
  Activity,
  AlertCircle,
  BarChart3
} from 'lucide-react';
import { getInitials } from '@loyalty/ui/lib/utils';

export const StatisticsPage: React.FC = () => {
  const { data: stats, isLoading } = useAdminStatistics();

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

  if (!stats) return null;

  return (
    <div className="space-y-8">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Statistics</h1>
        <p className="text-muted-foreground mt-2">
          Detailed analytics and insights for the loyalty program
        </p>
      </div>

      {/* Key Metrics */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatsCard
          title="Total Users"
          value={stats.users.total.toLocaleString()}
          description={`${stats.users.with_achievements} with achievements`}
          icon={<Users className="h-5 w-5" />}
        />
        <StatsCard
          title="Total Achievements"
          value={stats.achievements.total.toLocaleString()}
          description={`${stats.achievements.total_unlocks} unlocks`}
          icon={<Trophy className="h-5 w-5" />}
        />
        <StatsCard
          title="Total Badges"
          value={stats.badges.total.toLocaleString()}
          description={`${stats.badges.total_earned} earned`}
          icon={<Award className="h-5 w-5" />}
        />
        <StatsCard
          title="Cashback Paid"
          value={`₦${stats.cashback.total_paid.toLocaleString()}`}
          description={`${stats.cashback.success_rate}% success rate`}
          icon={<Wallet className="h-5 w-5" />}
        />
      </div>

      {/* Engagement Metrics */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Activity className="h-5 w-5" />
              Engagement Metrics
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">
                Avg. Achievements per User
              </span>
              <span className="text-2xl font-bold">
                {stats.engagement.avg_achievements_per_user.toFixed(1)}
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Active Users</span>
              <span className="text-2xl font-bold">
                {stats.users.with_achievements}
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">
                Participation Rate
              </span>
              <span className="text-2xl font-bold">
                {((stats.users.with_achievements / stats.users.total) * 100).toFixed(1)}%
              </span>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="h-5 w-5" />
              Cashback Performance
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Total Paid</span>
              <span className="text-2xl font-bold text-green-600">
                ₦{stats.cashback.total_paid.toLocaleString()}
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Pending</span>
              <span className="text-2xl font-bold text-orange-600">
                ₦{stats.cashback.pending.toLocaleString()}
              </span>
            </div>
            {stats.cashback.failed > 0 && (
              <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground flex items-center gap-1">
                  <AlertCircle className="h-4 w-4" />
                  Failed Transactions
                </span>
                <span className="text-2xl font-bold text-red-600">
                  {stats.cashback.failed}
                </span>
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <BarChart3 className="h-5 w-5" />
              Achievement Types
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {Object.entries(stats.achievements.by_type).map(([type, count]) => (
              <div key={type} className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground capitalize">{type}</span>
                <span className="text-2xl font-bold">{count}</span>
              </div>
            ))}
          </CardContent>
        </Card>
      </div>

      {/* Top Achievers */}
      <Card>
        <CardHeader>
          <CardTitle>Top Achievers 🏆</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <TableHead>Points</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {stats.engagement.top_achievers.map((user, index) => (
                <TableRow key={user.id}>
                  <TableCell className="font-medium">
                    <div className="flex items-center gap-3">
                      <Avatar className="h-8 w-8">
                        <AvatarFallback>
                          {getInitials(user.name)}
                        </AvatarFallback>
                      </Avatar>
                      <div>
                        <div className="flex items-center gap-2">
                          <span>{user.name}</span>
                          {index === 0 && <span className="text-xl">🥇</span>}
                          {index === 1 && <span className="text-xl">🥈</span>}
                          {index === 2 && <span className="text-xl">🥉</span>}
                        </div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">
                      {user.total_points.toLocaleString()} pts
                    </Badge>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Badge Levels */}
      <Card>
        <CardHeader>
          <CardTitle>Badge Levels</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Level</TableHead>
                <TableHead>Total Earned</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {Object.entries(stats.badges.by_level).map(([level, count]) => (
                <TableRow key={level}>
                  <TableCell>Level {level}</TableCell>
                  <TableCell>{count}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
};