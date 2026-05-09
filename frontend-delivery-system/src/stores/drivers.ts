import { defineStore } from 'pinia'
import { ref } from 'vue'
import { driversApi } from '../api/drivers'

export const useDriversStore = defineStore('drivers', () => {
  const drivers = ref<any[]>([])
  const currentDriver = ref<any>(null)
  const liveLocations = ref<any[]>([])
  const pagination = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchDrivers(params?: Record<string, any>) {
    loading.value = true
    error.value = null
    try {
      const res = await driversApi.list(params)
      drivers.value = res.data.data.data
      pagination.value = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page,
        per_page: res.data.data.per_page,
        total: res.data.data.total,
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load drivers'
    } finally {
      loading.value = false
    }
  }

  async function fetchDriver(id: number) {
    loading.value = true
    try {
      const res = await driversApi.get(id)
      currentDriver.value = res.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load driver'
    } finally {
      loading.value = false
    }
  }

  async function fetchLiveLocations() {
    try {
      const res = await driversApi.liveLocations()
      liveLocations.value = res.data.data
    } catch {}
  }

  async function createDriver(data: Record<string, any>) {
    const res = await driversApi.create(data)
    return res.data
  }

  async function updateStatus(id: number, status: string) {
    const res = await driversApi.updateStatus(id, status)
    return res.data
  }

  return {
    drivers, currentDriver, liveLocations, pagination, loading, error,
    fetchDrivers, fetchDriver, fetchLiveLocations, createDriver, updateStatus,
  }
})
