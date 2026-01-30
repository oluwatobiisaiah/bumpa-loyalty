import { Routes, Route, Navigate } from 'react-router-dom';
import { useAdminAuthStore } from './store/admin-auth-store';
import { AdminDashboardLayout } from './components/layouts/admin-dashboard-layout';
import { AdminAuthLayout } from './components/layouts/admin-auth-layout';
import { AdminLoginPage } from './pages/auth/admin-login';
import { AdminDashboardPage } from './pages/dashboard';
import { UsersPage } from './pages/users';
import { UserDetailPage } from './pages/user-detail';
import { StatisticsPage } from './pages/statistics';
import { AchievementsManagementPage } from './pages/achievements-management';
import { BadgeManagementPage } from './pages/badge-management';

function App() {
  const { isAuthenticated } = useAdminAuthStore();

  return (
    <Routes>
      {/* Public Routes */}
      <Route
        element={
          isAuthenticated ? <Navigate to="/admin/dashboard" replace /> : <AdminAuthLayout />
        }
      >
        <Route path="/admin/login" element={<AdminLoginPage />} />
      </Route>

      {/* Protected Admin Routes */}
      <Route
        element={
          isAuthenticated ? <AdminDashboardLayout /> : <Navigate to="/admin/login" replace />
        }
      >
        <Route path="/admin/dashboard" element={<AdminDashboardPage />} />
        <Route path="/admin/users" element={<UsersPage />} />
        <Route path="/admin/users/:userId" element={<UserDetailPage />} />
        <Route path="/admin/statistics" element={<StatisticsPage />} />
        <Route path="/admin/achievements" element={<AchievementsManagementPage />} />
        <Route path="/admin/badges" element={<BadgeManagementPage />} />
      </Route>

      {/* Default redirects */}
      <Route path="/" element={<Navigate to="/admin/dashboard" replace />} />
      <Route path="/admin" element={<Navigate to="/admin/dashboard" replace />} />
      <Route path="*" element={<Navigate to="/admin/dashboard" replace />} />
    </Routes>
  );
}

export default App;