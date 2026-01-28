import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '../lib/api-client';
import type { PurchaseFormData } from '@loyalty/types';
import { toast } from '@loyalty/ui';

export const usePurchases = (page = 1) => {
  return useQuery({
    queryKey: ['purchases', page],
    queryFn: () => apiClient.getPurchases(page),
  });
};

export const useCreatePurchase = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: PurchaseFormData) => apiClient.createPurchase(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['purchases'] });
      queryClient.invalidateQueries({ queryKey: ['loyalty-dashboard'] });
      toast({ title: 'Success', description: 'Purchase created successfully!' });
    },
    onError: (error) => {
      toast({ title: 'Error', description: error instanceof Error ? error.message : 'Failed to create purchase', variant: 'destructive' });
    },
  });
};