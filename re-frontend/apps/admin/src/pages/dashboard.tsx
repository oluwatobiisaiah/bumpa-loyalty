// apps/admin/src/pages/dashboard.tsx

import React from 'react';
import { Link } from 'react-router-dom';
import { useAdminStatistics } from '../hooks/use-admin-statistics';
import { useAdminUsers } from '../hooks/use-admin-users';
import {
  StatsCard,
  Button,
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
  ArrowRight,
  Activity,
  AlertCircle,
} from 'lucide-react';

export const AdminDashboardPage: React.FC = () => {
  const { data: stats, isLoading: statsLoading } = useAdminStatistics();
  const { data: usersData, isLoading: usersLoading } = useAdminUsers();

  if (statsLoading) {
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
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
          <p className="text-muted-foreground">
            Overview of loyalty program performance
          </p>
        </div>
        <Button asChild>
          <Link to="/admin/statistics">
            View Full Statistics
            <ArrowRight className="ml-2 h-4 w-4" />
          </Link>
        </Button>
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
      <div className="grid gap-4 md:grid-cols-2">
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
      </div>

      {/* Top Achievers */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Top Achievers 🏆</CardTitle>
            <Button variant="ghost" size="sm" asChild>
              <Link to="/admin/users">
                View All
                <ArrowRight className="ml-2 h-4 w-4" />
              </Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <TableHead>Points</TableHead>
                <TableHead>Achievements</TableHead>
                <TableHead>Badge</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {stats.engagement.top_achievers.map((user, index) => (
                <TableRow key={user.id}>
                  <TableCell className="font-medium">
                    <div className="flex items-center gap-3">
                      <Avatar className="h-8 w-8">
                        <AvatarFallback>
                          {user.name.charAt(0).toUpperCase()}
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
                  <TableCell>-</TableCell>
                  <TableCell>-</TableCell>
                  <TableCell className="text-right">
                    <Button variant="ghost" size="sm" asChild>
                      <Link to={`/admin/users/${user.id}`}>View Details</Link>
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Recent Users */}
      {!usersLoading && usersData && (
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle>Recent Users</CardTitle>
              <Button variant="ghost" size="sm" asChild>
                <Link to="/admin/users">
                  View All
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>User</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Points</TableHead>
                  <TableHead>Achievements</TableHead>
                  <TableHead>Badge</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {usersData.data.slice(0, 5).map((user) => (
                  <TableRow key={user.id}>
                    <TableCell className="font-medium">
                      <div className="flex items-center gap-3">
                        <Avatar className="h-8 w-8">
                          <AvatarFallback>
                            {user.name.charAt(0).toUpperCase()}
                          </AvatarFallback>
                        </Avatar>
                        <span>{user.name}</span>
                      </div>
                    </TableCell>
                    <TableCell className="text-muted-foreground">
                      {user.email}
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline">
                        {user.total_points.toLocaleString()}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      {user.achievements_count}
                    </TableCell>
                    <TableCell>
                      {user.current_badge ? (
                        <Badge>
                          {user.current_badge.icon} {user.current_badge.name}
                        </Badge>
                      ) : (
                        <span className="text-muted-foreground">None</span>
                      )}
                    </TableCell>
                    <TableCell className="text-right">
                      <Button variant="ghost" size="sm" asChild>
                        <Link to={`/admin/users/${user.id}`}>View</Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}
    </div>
  );
};