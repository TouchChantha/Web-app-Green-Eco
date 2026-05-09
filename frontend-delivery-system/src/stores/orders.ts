import { defineStore } from 'pinia'
import { ref } from 'vue'
import { ordersApi } from '../api/orders'

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref<any[]>([])
  const currentOrder = ref<any>(null)
  const pagination = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchOrders(params?: Record<string, any>) {
    loading.value = true
    error.value = null
    try {
      const res = await ordersApi.list(params)
      orders.value = res.data.data.data
      pagination.value = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page,
        per_page: res.data.data.per_page,
        total: res.data.data.total,
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load orders'
    } finally {
      loading.value = false
    }
  }

  async function fetchOrder(id: number) {
    loading.value = true
    error.value = null
    try {
      const res = await ordersApi.get(id)
      currentOrder.value = res.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load order'
    } finally {
      loading.value = false
    }
  }

  async function createOrder(data: Record<string, any>) {
    const res = await ordersApi.create(data)
    return res.data
  }

  async function updateOrder(id: number, data: Record<string, any>) {
    const res = await ordersApi.update(id, data)
    return res.data
  }

  async function assignDriver(orderId: number, driverId: number) {
    const res = await ordersApi.assign(orderId, driverId)
    return res.data
  }

  async function updateStatus(orderId: number, data: Record<string, any>) {
    const res = await ordersApi.updateStatus(orderId, data)
    return res.data
  }

  async function cancelOrder(orderId: number, reason?: string) {
    const res = await ordersApi.cancel(orderId, reason)
    return res.data
  }

  return {
    orders, currentOrder, pagination, loading, error,
    fetchOrders, fetchOrder, createOrder, updateOrder,
    assignDriver, updateStatus, cancelOrder,
  }
})
