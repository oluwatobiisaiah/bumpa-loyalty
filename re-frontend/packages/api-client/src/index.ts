import axios from 'axios';
import type { AxiosInstance, AxiosError, InternalAxiosRequestConfig } from 'axios';
import type {
  User,
  AuthResponse,
  LoginCredentials,
  RegisterData,
  LoyaltyDashboard,
  Purchase,
  PurchaseFormData,
  ApiResponse,
  PaginatedResponse,
  AdminUserSummary,
  AdminStatistics,
  UserFilters,
  ApiError,
} from '@loyalty/types';

/**
 * API Client Configuration
 */
interface ApiClientConfig {
  baseURL: string;
  timeout?: number;
  headers?: Record<string, string>;
}

/**
 * API Client Class
 * Handles all HTTP requests to the backend API
 */
export class apiClient {
  private client: AxiosInstance;
  private token: string | null = null;

  constructor(config: ApiClientConfig) {
    this.client = axios.create({
      baseURL: config.baseURL,
      timeout: config.timeout || 30000,
      headers: {
        'Content-Type': 'application/json',
        ...config.headers,
      },
    });

    this.setupInterceptors();
    this.loadToken();
  }

  /**
   * Setup request and response interceptors
   */
  private setupInterceptors(): void {
    // Request interceptor - Add auth token
    this.client.interceptors.request.use(
      (config: InternalAxiosRequestConfig) => {
        if (this.token && config.headers) {
          config.headers.Authorization = `Bearer ${this.token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor - Handle errors
    this.client.interceptors.response.use(
      (response) => response,
      async (error: AxiosError<ApiError>) => {
        if (error.response?.status === 401) {
          this.clearToken();
          window.location.href = '/login';
        }
        return Promise.reject(this.handleError(error));
      }
    );
  }

  /**
   * Handle API errors
   */
  private handleError(error: AxiosError<ApiError>): Error {
    if (error.response) {
      const message = error.response.data?.message || 'An error occurred';
      return new Error(message);
    }
    if (error.request) {
      return new Error('No response from server. Please check your connection.');
    }
    return new Error(error.message || 'An unexpected error occurred');
  }

  /**
   * Token management
   */
  setToken(token: string): void {
    this.token = token;
    localStorage.setItem('auth_token', token);
  }

  getToken(): string | null {
    return this.token;
  }

  clearToken(): void {
    this.token = null;
    localStorage.removeItem('auth_token');
  }

  private loadToken(): void {
    const token = localStorage.getItem('auth_token');
    if (token) {
      this.token = token;
    }
  }

  /**
   * Authentication Endpoints
   */
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const { data } = await this.client.post<ApiResponse<AuthResponse>>('/v1/login', credentials);
    this.setToken(data.data.token);
    return data.data;
  }

  async register(userData: RegisterData): Promise<AuthResponse> {
    const { data } = await this.client.post<ApiResponse<AuthResponse>>('/v1/register', userData);
    this.setToken(data.data.token);
    return data.data;
  }

  async logout(): Promise<void> {
    await this.client.post('/v1/logout');
    this.clearToken();
  }

  async getCurrentUser(): Promise<User> {
    const { data } = await this.client.get<ApiResponse<User>>('/v1/user');
    return data.data;
  }

  /**
   * Customer Loyalty Endpoints
   */
  async getLoyaltyDashboard(): Promise<LoyaltyDashboard> {
    const { data } = await this.client.get<ApiResponse<LoyaltyDashboard>>('/v1/loyalty/dashboard');
    return data.data;
  }

  async getUserAchievements(userId: number): Promise<LoyaltyDashboard> {
    const { data } = await this.client.get<ApiResponse<LoyaltyDashboard>>(
      `/v1/users/${userId}/achievements`
    );
    return data.data;
  }

  async getUserBadges(userId: number): Promise<any> {
    const { data } = await this.client.get(`/v1/users/${userId}/badges`);
    return data.data;
  }

  async getUserCashback(userId: number): Promise<any> {
    const { data } = await this.client.get(`/v1/users/${userId}/cashback`);
    return data.data;
  }

  /**
   * Purchase Endpoints
   */
  async getPurchases(page = 1): Promise<PaginatedResponse<Purchase>> {
    const { data } = await this.client.get<PaginatedResponse<Purchase>>(
      `/v1/purchases?page=${page}`
    );
    return data;
  }

  async createPurchase(purchaseData: PurchaseFormData): Promise<Purchase> {
    const { data } = await this.client.post<ApiResponse<Purchase>>('/v1/purchases', purchaseData);
    return data.data;
  }

  async getPurchase(id: number): Promise<Purchase> {
    const { data } = await this.client.get<ApiResponse<Purchase>>(`/v1/purchases/${id}`);
    return data.data;
  }

  async getPurchaseStatistics(): Promise<any> {
    const { data } = await this.client.get('/v1/purchases/statistics');
    return data.data;
  }

  /**
   * Admin Authentication Endpoints
   */
  async adminLogin(credentials: LoginCredentials): Promise<AuthResponse> {
    const { data } = await this.client.post<ApiResponse<AuthResponse>>(
      '/v1/admin/login',
      credentials
    );
    this.setToken(data.data.token);
    return data.data;
  }

  async adminLogout(): Promise<void> {
    await this.client.post('/v1/admin/logout');
    this.clearToken();
  }

  /**
   * Admin User Management Endpoints
   */
  async getAdminUsers(filters?: UserFilters): Promise<PaginatedResponse<AdminUserSummary>> {
    const params = new URLSearchParams();
    if (filters?.search) params.append('search', filters.search);
    if (filters?.sort_by) params.append('sort_by', filters.sort_by);
    if (filters?.sort_order) params.append('sort_order', filters.sort_order);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const { data } = await this.client.get<PaginatedResponse<AdminUserSummary>>(
      `/v1/admin/users?${params.toString()}`
    );
    return data;
  }

  async getAdminUserDetails(userId: number): Promise<any> {
    const { data } = await this.client.get(`/v1/admin/users/${userId}/loyalty`);
    return data.data;
  }

  async getAdminStatistics(): Promise<AdminStatistics> {
    const { data } = await this.client.get<ApiResponse<AdminStatistics>>(
      '/v1/admin/loyalty/stats'
    );
    return data.data;
  }
}

/**
 * Create API client instance
 */
export const createApiClient = (config: ApiClientConfig): apiClient => {
  return new apiClient(config);
};

/**
 * Default export
 */
export default apiClient;