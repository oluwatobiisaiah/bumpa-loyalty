import { useQuery } from '@tanstack/react-query';
import { apiClient } from '../lib/api-client';

export const useAdminStatistics = () => {
  return useQuery({
    queryKey: ['admin-statistics'],
    queryFn: () => apiClient.getAdminStatistics(),
    refetchInterval: 60000, // Refetch every minute
  });
};