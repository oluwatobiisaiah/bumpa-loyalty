export interface User {
  id: number;
  name: string;
  email: string;
  role: 'customer' | 'admin';
  total_points: number;
  total_cashback: number;
  current_badge?: Badge;
  achievements_count?: number;
  badges_count?: number;
  created_at?: string;
  updated_at?: string;
}

/**
 * Achievement Types
 */
export interface Achievement {
  id: number;
  name: string;
  description: string;
  type: 'purchase' | 'spending' | 'referral' | 'review' | 'streak';
  tier: 'bronze' | 'silver' | 'gold' | 'platinum';
  points: number;
  icon: string;
  criteria: {
    target: number;
    [key: string]: any;
  };
  is_active: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface AchievementProgress extends Achievement {
  progress: {
    current: number;
    required: number;
    percentage: number;
    unlocked: boolean;
    unlocked_at?: string;
  };
}

export interface Badge {
  id: number;
  name: string;
  description: string;
  level: number;
  points_required: number;
  achievements_required: number;
  icon: string;
  color: string;
  benefits: string[];
  is_active: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface BadgeProgress extends Badge {
  progress: {
    points: {
      current: number;
      required: number;
      percentage: number;
    };
    achievements: {
      current: number;
      required: number;
      percentage: number;
    };
    overall_percentage: number;
    earned: boolean;
  };
  is_current?: boolean;
}


export interface Purchase {
  id: number;
  user_id: number;
  order_id: string;
  amount: number;
  currency: string;
  status: 'pending' | 'completed' | 'failed' | 'refunded';
  items: PurchaseItem[];
  metadata?: Record<string, any>;
  processed_for_loyalty: boolean;
  created_at: string;
  updated_at: string;
}

export interface PurchaseItem {
  name: string;
  quantity: number;
  price: number;
}


export interface CashbackTransaction {
  id: number;
  user_id: number;
  purchase_id?: number;
  amount: number;
  currency: string;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  payment_provider: string;
  payment_reference?: string;
  error_message?: string;
  processed_at?: string;
  created_at: string;
  updated_at: string;
}

export interface CashbackSummary {
  total_earned: number;
  pending: number;
  completed: number;
  failed: number;
  transaction_count: number;
  recent_transactions: Array<{
    id: number;
    amount: number;
    status: string;
    created_at: string;
    processed_at?: string;
  }>;
}

/**
 * API Response Types
 */
export interface ApiResponse<T = any> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T = any> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

/**
 * Auth Types
 */
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  token_type: string;
}


export interface LoyaltyDashboard {
  user: User;
  achievements: {
    progress: AchievementProgress[];
    summary: {
      total_achievements: number;
      unlocked_achievements: number;
      locked_achievements: number;
      completion_percentage: number;
    };
    recently_unlocked: Achievement[];
  };
  badges: {
    current: Badge | null;
    progress: BadgeProgress[];
    history: Array<{
      id: number;
      name: string;
      level: number;
      icon: string;
      earned_at: string;
      is_current: boolean;
    }>;
    summary: {
      current_badge: Badge | null;
      next_badge: {
        id: number;
        name: string;
        level: number;
        progress: BadgeProgress['progress'];
      } | null;
      total_badges_earned: number;
    };
  };
  cashback: CashbackSummary;
}

/**
 * Admin Dashboard Types
 */
export interface AdminUserSummary {
  id: number;
  name: string;
  email: string;
  total_points: number;
  total_cashback: number;
  achievements_count: number;
  current_badge: Badge | null;
  member_since: string;
  achievement_progress: {
    total_achievements: number;
    unlocked_achievements: number;
    completion_percentage: number;
  };
}

export interface AdminStatistics {
  users: {
    total: number;
    with_achievements: number;
    with_badges: number;
  };
  achievements: {
    total: number;
    active: number;
    total_unlocks: number;
    by_type: Record<string, number>;
  };
  badges: {
    total: number;
    active: number;
    total_earned: number;
    by_level: Record<number, number>;
  };
  cashback: {
    total_paid: number;
    pending: number;
    failed: number;
    success_rate: number;
  };
  engagement: {
    avg_achievements_per_user: number;
    top_achievers: Array<{
      id: number;
      name: string;
      total_points: number;
    }>;
  };
}

/**
 * Notification Types
 */
export interface Notification {
  id: string;
  type: 'achievement_unlocked' | 'badge_unlocked' | 'cashback_processed' | 'cashback_failed';
  data: {
    achievement?: Achievement;
    badge?: Badge;
    transaction?: CashbackTransaction;
    message: string;
    points_earned?: number;
    total_points?: number;
    amount?: number;
  };
  read_at: string | null;
  created_at: string;
}

/**
 * WebSocket Event Types
 */
export interface WebSocketEvent {
  event: string;
  data: any;
}

export interface AchievementUnlockedEvent {
  user_id: number;
  user_name: string;
  achievement: Achievement;
  total_points: number;
  unlocked_at: string;
}

export interface BadgeUnlockedEvent {
  user_id: number;
  user_name: string;
  badge: Badge;
  earned_at: string;
}

/**
 * Form Types
 */
export interface PurchaseFormData {
  amount: number;
  currency?: string;
  items: PurchaseItem[];
}

/**
 * Filter and Sort Types
 */
export interface UserFilters {
  search?: string;
  sort_by?: 'total_points' | 'total_cashback' | 'name' | 'created_at' | 'achievements_count';
  sort_order?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

/**
 * Chart Data Types
 */
export interface ChartDataPoint {
  label: string;
  value: number;
  color?: string;
}

export interface TimeSeriesData {
  date: string;
  value: number;
}

/**
 * Utility Types
 */
export type LoadingState = 'idle' | 'loading' | 'success' | 'error';

export interface AsyncState<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
}