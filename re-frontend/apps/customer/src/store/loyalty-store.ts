import { create } from 'zustand';
import type { LoyaltyDashboard } from '@loyalty/types';

interface LoyaltyState {
  dashboard: LoyaltyDashboard | null;
  isLoading: boolean;
  error: string | null;
  setDashboard: (dashboard: LoyaltyDashboard) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  reset: () => void;
}

export const useLoyaltyStore = create<LoyaltyState>((set) => ({
  dashboard: null,
  isLoading: false,
  error: null,
  
  setDashboard: (dashboard) => set({ dashboard, isLoading: false, error: null }),
  setLoading: (isLoading) => set({ isLoading }),
  setError: (error) => set({ error, isLoading: false }),
  reset: () => set({ dashboard: null, isLoading: false, error: null }),
}));
