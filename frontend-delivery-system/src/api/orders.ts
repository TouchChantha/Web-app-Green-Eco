import api from './axios'

export const ordersApi = {
  list: (params?: Record<string, any>) => api.get('/orders', { params }),

  get: (id: number) => api.get(`/orders/${id}`),

  create: (data: Record<string, any>) => api.post('/orders', data),

  update: (id: number, data: Record<string, any>) => api.put(`/orders/${id}`, data),

  assign: (id: number, driverId: number) =>
    api.post(`/orders/${id}/assign`, { driver_id: driverId }),

  updateStatus: (id: number, data: Record<string, any>) =>
    api.post(`/orders/${id}/status`, data),

  cancel: (id: number, reason?: string) =>
    api.post(`/orders/${id}/cancel`, { reason }),
}
