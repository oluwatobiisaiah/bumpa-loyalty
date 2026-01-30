import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAdminUsers } from '../hooks/use-admin-users';
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
import { Search, ChevronLeft, ChevronRight, ArrowUpDown } from 'lucide-react';
import { getInitials, formatCurrency } from '@loyalty/ui/lib/utils';
import type { UserFilters } from '@loyalty/types';

export const UsersPage: React.FC = () => {
  const { data, isLoading, filters, updateFilters } = useAdminUsers();
  const [searchInput, setSearchInput] = useState('');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    updateFilters({ search: searchInput, page: 1 });
  };

  const handleSort = (field: UserFilters['sort_by']) => {
    updateFilters({
      sort_by: field,
      sort_order: filters.sort_by === field && filters.sort_order === 'desc' ? 'asc' : 'desc',
    });
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  const users = data?.data || [];
  const meta = data?.meta;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Users</h1>
        <p className="text-muted-foreground mt-2">
          Manage and view all loyalty program users
        </p>
      </div>

      {/* Search */}
      <form onSubmit={handleSearch} className="flex gap-2">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            type="search"
            placeholder="Search by name or email..."
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            className="pl-9"
          />
        </div>
        <Button type="submit">Search</Button>
        {filters.search && (
          <Button
            variant="outline"
            onClick={() => {
              setSearchInput('');
              updateFilters({ search: '', page: 1 });
            }}
          >
            Clear
          </Button>
        )}
      </form>

      {/* Users Table */}
      <Card>
        <CardHeader>
          <CardTitle>All Users ({meta?.total || 0})</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <TableHead>
                  <button
                    onClick={() => handleSort('total_points')}
                    className="flex items-center gap-1 hover:text-foreground"
                  >
                    Points
                    <ArrowUpDown className="h-3 w-3" />
                  </button>
                </TableHead>
                <TableHead>
                  <button
                    onClick={() => handleSort('total_cashback')}
                    className="flex items-center gap-1 hover:text-foreground"
                  >
                    Cashback
                    <ArrowUpDown className="h-3 w-3" />
                  </button>
                </TableHead>
                <TableHead>
                  <button
                    onClick={() => handleSort('achievements_count')}
                    className="flex items-center gap-1 hover:text-foreground"
                  >
                    Achievements
                    <ArrowUpDown className="h-3 w-3" />
                  </button>
                </TableHead>
                <TableHead>Badge</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {users.map((user) => (
                <TableRow key={user.id}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <Avatar>
                        <AvatarFallback>{getInitials(user.name)}</AvatarFallback>
                      </Avatar>
                      <div>
                        <p className="font-medium">{user.name}</p>
                        <p className="text-sm text-muted-foreground">{user.email}</p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{user.total_points.toLocaleString()}</Badge>
                  </TableCell>
                  <TableCell>{formatCurrency(user.total_cashback)}</TableCell>
                  <TableCell>{user.achievements_count}</TableCell>
                  <TableCell>
                    {user.current_badge ? (
                      <Badge>
                        {user.current_badge.icon} {user.current_badge.name}
                      </Badge>
                    ) : (
                      <span className="text-muted-foreground text-sm">None</span>
                    )}
                  </TableCell>
                  <TableCell className="text-right">
                    <Button variant="ghost" size="sm" asChild>
                      <Link to={`/admin/users/${user.id}`}>View Details</Link>
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>

          {/* Pagination */}
          {meta && meta.last_page > 1 && (
            <div className="flex items-center justify-between mt-4">
              <p className="text-sm text-muted-foreground">
                Showing {meta.from} to {meta.to} of {meta.total} results
              </p>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => updateFilters({ page: filters.page! - 1 })}
                  disabled={filters.page === 1}
                >
                  <ChevronLeft className="h-4 w-4 mr-1" />
                  Previous
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => updateFilters({ page: filters.page! + 1 })}
                  disabled={filters.page === meta.last_page}
                >
                  Next
                  <ChevronRight className="h-4 w-4 ml-1" />
                </Button>
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};