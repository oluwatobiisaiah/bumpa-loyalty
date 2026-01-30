import React from 'react';
import { useParams, Link } from 'react-router-dom';
import { useAdminUserDetail } from '../hooks/use-admin-users';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  Badge,
  Avatar,
  AvatarFallback,
  Button,
  Skeleton,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@loyalty/ui';
import {
  ArrowLeft,
  Trophy,
  Award,
  Wallet,
  Calendar,
  Mail,
  User,
} from 'lucide-react';
import { getInitials, formatCurrency, formatDate } from '@loyalty/ui/lib/utils';

export const UserDetailPage: React.FC = () => {
  const { userId } = useParams<{ userId: string }>();
  const { data: userDetail, isLoading } = useAdminUserDetail(Number(userId));

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <div className="grid gap-6 md:grid-cols-3">
          {[...Array(3)].map((_, i) => (
            <Skeleton key={i} className="h-32" />
          ))}
        </div>
        <Skeleton className="h-96" />
      </div>
    );
  }

  if (!userDetail) {
    return (
      <div className="text-center py-12">
        <p className="text-muted-foreground">User not found</p>
        <Link to="/admin/users">
          <Button variant="outline" className="mt-4">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Users
          </Button>
        </Link>
      </div>
    );
  }

  const { user, achievements, badges, activity } = userDetail;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Link to="/admin/users">
            <Button variant="ghost" size="icon">
              <ArrowLeft className="h-5 w-5" />
            </Button>
          </Link>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">User Details</h1>
            <p className="text-muted-foreground mt-1">
              Complete profile and activity history
            </p>
          </div>
        </div>
      </div>

      {/* User Profile Card */}
      <Card>
        <CardHeader>
          <CardTitle>Profile Information</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-start gap-6">
            <Avatar className="h-20 w-20">
              <AvatarFallback className="text-2xl">
                {getInitials(user.name)}
              </AvatarFallback>
            </Avatar>

            <div className="flex-1 grid gap-6 md:grid-cols-2">
              <div className="space-y-4">
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
                    <p className="font-medium">{formatDate(user.member_since)}</p>
                  </div>
                </div>
              </div>

              <div className="space-y-4">
                <div className="flex items-center gap-3">
                  <Trophy className="h-5 w-5 text-primary" />
                  <div>
                    <p className="text-sm text-muted-foreground">Total Points</p>
                    <p className="text-2xl font-bold">{user.total_points.toLocaleString()}</p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <Wallet className="h-5 w-5 text-green-600" />
                  <div>
                    <p className="text-sm text-muted-foreground">Total Cashback</p>
                    <p className="text-2xl font-bold text-green-600">
                      {formatCurrency(user.total_cashback)}
                    </p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <Award className="h-5 w-5 text-blue-600" />
                  <div>
                    <p className="text-sm text-muted-foreground">Current Badge</p>
                    {badges.current ? (
                      <Badge variant="default" className="mt-1">
                        {badges.current.icon} {badges.current.name}
                      </Badge>
                    ) : (
                      <span className="text-sm text-muted-foreground">None</span>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Tabs */}
      <Tabs defaultValue="achievements" className="space-y-4">
        <TabsList>
          <TabsTrigger value="achievements">
            Achievements ({achievements.total})
          </TabsTrigger>
          <TabsTrigger value="badges">
            Badges ({badges.earned.length})
          </TabsTrigger>
          <TabsTrigger value="purchases">
            Purchases ({activity.total_purchases})
          </TabsTrigger>
          <TabsTrigger value="cashback">
            Cashback ({activity.cashback_transactions.length})
          </TabsTrigger>
        </TabsList>

        {/* Achievements Tab */}
        <TabsContent value="achievements" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Unlocked Achievements</CardTitle>
            </CardHeader>
            <CardContent>
              {achievements.list.length > 0 ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Achievement</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Tier</TableHead>
                      <TableHead>Points</TableHead>
                      <TableHead>Unlocked</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {achievements.list.map((achievement) => (
                      <TableRow key={achievement.id}>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <span className="text-2xl">{achievement.icon || '🏆'}</span>
                            <span className="font-medium">{achievement.name}</span>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className="capitalize">
                            {achievement.type}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <Badge variant="secondary" className="capitalize">
                            {achievement.tier}
                          </Badge>
                        </TableCell>
                        <TableCell>{achievement.points}</TableCell>
                        <TableCell>{formatDate(achievement.unlocked_at)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <p className="text-center text-muted-foreground py-8">
                  No achievements unlocked yet
                </p>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Badges Tab */}
        <TabsContent value="badges" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Badge History</CardTitle>
            </CardHeader>
            <CardContent>
              {badges.earned.length > 0 ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Badge</TableHead>
                      <TableHead>Level</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Earned</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {badges.earned.map((badge) => (
                      <TableRow key={badge.id}>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <span className="text-2xl">{badge.icon}</span>
                            <span className="font-medium">{badge.name}</span>
                          </div>
                        </TableCell>
                        <TableCell>Level {badge.level}</TableCell>
                        <TableCell>
                          {badge.is_current && (
                            <Badge variant="default">Current</Badge>
                          )}
                        </TableCell>
                        <TableCell>{formatDate(badge.earned_at)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <p className="text-center text-muted-foreground py-8">
                  No badges earned yet
                </p>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Purchases Tab */}
        <TabsContent value="purchases" className="space-y-4">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Purchase History</CardTitle>
                <div className="text-sm text-muted-foreground">
                  Total Spent: {formatCurrency(activity.total_spent)}
                </div>
              </div>
            </CardHeader>
            <CardContent>
              {activity.recent_purchases.length > 0 ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Order ID</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Date</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {activity.recent_purchases.map((purchase) => (
                      <TableRow key={purchase.id}>
                        <TableCell className="font-mono text-sm">
                          #{purchase.id}
                        </TableCell>
                        <TableCell className="font-semibold">
                          {formatCurrency(purchase.amount)}
                        </TableCell>
                        <TableCell>
                          <Badge
                            variant={
                              purchase.status === 'completed' ? 'default' : 'secondary'
                            }
                          >
                            {purchase.status}
                          </Badge>
                        </TableCell>
                        <TableCell>{formatDate(purchase.created_at)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <p className="text-center text-muted-foreground py-8">
                  No purchases yet
                </p>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Cashback Tab */}
        <TabsContent value="cashback" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Cashback Transactions</CardTitle>
            </CardHeader>
            <CardContent>
              {activity.cashback_transactions.length > 0 ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Transaction ID</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Processed</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {activity.cashback_transactions.map((transaction) => (
                      <TableRow key={transaction.id}>
                        <TableCell className="font-mono text-sm">
                          #{transaction.id}
                        </TableCell>
                        <TableCell className="font-semibold text-green-600">
                          {formatCurrency(transaction.amount)}
                        </TableCell>
                        <TableCell>
                          <Badge
                            variant={
                              transaction.status === 'completed'
                                ? 'default'
                                : transaction.status === 'failed'
                                ? 'destructive'
                                : 'secondary'
                            }
                          >
                            {transaction.status}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {transaction.processed_at
                            ? formatDate(transaction.processed_at)
                            : 'Pending'}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <p className="text-center text-muted-foreground py-8">
                  No cashback transactions yet
                </p>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};