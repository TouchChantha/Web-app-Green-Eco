import api from './axios'

export const reportsApi = {
  dashboard: () => api.get('/reports/dashboard'),

  ordersReport: (params: Record<string, any>) =>
    api.get('/reports/orders', { params }),

  driverPerformance: (driverId: number, params?: Record<string, any>) =>
    api.get(`/reports/drivers/${driverId}/performance`, { params }),

  generateDaily: (date?: string) =>
    api.post('/reports/generate-daily', { date }),
}
