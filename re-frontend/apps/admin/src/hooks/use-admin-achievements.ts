import { useQuery } from '@tanstack/react-query';
import { apiClient } from '../lib/api-client';

export const useAdminAchievements = () => {
  return useQuery({
    queryKey: ['admin-achievements'],
    queryFn: () => apiClient.getAdminUserAchievements(),
    refetchInterval: 60000, // Refetch every minute
  });
};