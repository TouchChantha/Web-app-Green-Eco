import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../api/auth'

export interface AuthUser {
  id: number
  name: string
  email: string
  role: 'admin' | 'driver'
  is_active: boolean
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<AuthUser | null>(
    JSON.parse(localStorage.getItem('user') || 'null')
  )
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isDriver = computed(() => user.value?.role === 'driver')

  async function login(email: string, password: string) {
    loading.value = true
    try {
      const res = await authApi.login(email, password)
      token.value = res.data.access_token
      user.value = res.data.user
      localStorage.setItem('token', res.data.access_token)
      localStorage.setItem('user', JSON.stringify(res.data.user))
      return res.data
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function refresh() {
    const res = await authApi.refresh()
    token.value = res.data.access_token
    user.value = res.data.user
    localStorage.setItem('token', res.data.access_token)
    localStorage.setItem('user', JSON.stringify(res.data.user))
    return res.data
  }

  async function fetchMe() {
    const res = await authApi.me()
    user.value = res.data.data
    localStorage.setItem('user', JSON.stringify(res.data.data))
  }

  return { token, user, loading, isAuthenticated, isAdmin, isDriver, login, logout, refresh, fetchMe }
})
