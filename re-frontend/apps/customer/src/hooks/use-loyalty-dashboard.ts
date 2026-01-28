import { useQuery } from '@tanstack/react-query';
import { apiClient } from '../lib/api-client';
import { useLoyaltyStore } from '../store/loyalty-store';

export const useLoyaltyDashboard = () => {
  const setDashboard = useLoyaltyStore((state) => state.setDashboard);

  return useQuery({
    queryKey: ['loyalty-dashboard'],
    queryFn: async () => {
      const data = await apiClient.getLoyaltyDashboard();
      setDashboard(data);
      return data;
    },
  });
};
