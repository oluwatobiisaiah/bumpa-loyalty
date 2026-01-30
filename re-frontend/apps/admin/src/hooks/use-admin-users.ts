import { useQuery} from '@tanstack/react-query';
import { useState } from 'react';
import { apiClient } from '../lib/api-client';
import type { UserFilters } from '@loyalty/types';

export const useAdminUsers = () => {
  const [filters, setFilters] = useState<UserFilters>({
    page: 1,
    per_page: 15,
    sort_by: 'total_points',
    sort_order: 'desc',
  });

  const query = useQuery({
    queryKey: ['admin-users', filters],
    queryFn: () => apiClient.getAdminUsers(filters),
  });

  const updateFilters = (newFilters: Partial<UserFilters>) => {
    setFilters((prev) => ({ ...prev, ...newFilters }));
  };

  return {
    ...query,
    filters,
    updateFilters,
  };
};

export const useAdminUserDetail = (userId: number) => {
  return useQuery({
    queryKey: ['admin-user-detail', userId],
    queryFn: () => apiClient.getAdminUserDetails(userId),
    enabled: !!userId,
  });
};
