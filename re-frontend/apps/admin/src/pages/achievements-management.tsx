import React, { useState } from 'react';
import { useAdminAchievements } from '../hooks/use-admin-achievements';
import {
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
  Button,
  Input,
  Skeleton,
} from '@loyalty/ui';
import { Search } from 'lucide-react';
import { getInitials, formatDate } from '@loyalty/ui/lib/utils';

export const AchievementsManagementPage: React.FC = () => {
  const { data, isLoading } = useAdminAchievements();
  const [searchInput, setSearchInput] = useState('');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // TODO: Implement search filtering
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  const achievements = data?.data || [];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Achievements Management</h1>
        <p className="text-muted-foreground mt-2">
          View and manage all user achievements
        </p>
      </div>

      {/* Search */}
      <form onSubmit={handleSearch} className="flex gap-2">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            type="search"
            placeholder="Search by user or achievement..."
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            className="pl-9"
          />
        </div>
        <Button type="submit">Search</Button>
        {searchInput && (
          <Button
            variant="outline"
            onClick={() => setSearchInput('')}
          >
            Clear
          </Button>
        )}
      </form>

      {/* Achievements Table */}
      <Card>
        <CardHeader>
          <CardTitle>All User Achievements ({achievements.length})</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <TableHead>Achievement</TableHead>
                <TableHead>Type</TableHead>
                <TableHead>Tier</TableHead>
                <TableHead>Points</TableHead>
                <TableHead>Unlocked At</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {achievements.map((item: any) => (
                <TableRow key={`${item.user.id}-${item.achievement.id}`}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <Avatar>
                        <AvatarFallback>{getInitials(item.user.name)}</AvatarFallback>
                      </Avatar>
                      <div>
                        <p className="font-medium">{item.user.name}</p>
                        <p className="text-sm text-muted-foreground">{item.user.email}</p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <span className="text-2xl">{item.achievement.icon || '🏆'}</span>
                      <span className="font-medium">{item.achievement.name}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline" className="capitalize">
                      {item.achievement.type}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary" className="capitalize">
                      {item.achievement.tier}
                    </Badge>
                  </TableCell>
                  <TableCell>{item.achievement.points}</TableCell>
                  <TableCell>{formatDate(item.unlocked_at)}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>

          {achievements.length === 0 && (
            <p className="text-center text-muted-foreground py-8">
              No achievements found
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  );
};