import api from './axios'

export const authApi = {
  login: (email: string, password: string) =>
    api.post('/auth/login', { email, password }),

  me: () => api.get('/auth/me'),

  permissions: () => api.get('/auth/permissions'),

  /** Refresh JWT token — shared use case (admin + driver) */
  refresh: () => api.post('/auth/refresh'),

  logout: () => api.post('/auth/logout'),
}
