import api from './axios'

export const driversApi = {
  list: (params?: Record<string, any>) => api.get('/drivers', { params }),

  get: (id: number) => api.get(`/drivers/${id}`),

  create: (data: Record<string, any>) => api.post('/drivers', data),

  updateStatus: (id: number, status: string) =>
    api.patch(`/drivers/${id}/status`, { status }),

  liveLocations: () => api.get('/drivers/live-locations'),

  locationHistory: (id: number, params?: Record<string, any>) =>
    api.get(`/drivers/${id}/location-history`, { params }),

  updateLocation: (data: Record<string, any>) =>
    api.post('/drivers/location', data),
}
