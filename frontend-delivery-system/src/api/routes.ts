import api from './axios'

export const routesApi = {
  show: (orderId: number) => api.get(`/routes/${orderId}`),

  eta: (orderId: number) => api.get(`/routes/${orderId}/eta`),

  reOptimize: (orderId: number) => api.post(`/routes/${orderId}/optimize`),
}
