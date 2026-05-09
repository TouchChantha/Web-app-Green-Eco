import { onMounted, onUnmounted, watch } from 'vue'
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { ordersApi } from '../api/orders'
import { driversApi } from '../api/drivers'

const TICK_MS = 30_000
const hasGeoSupport = typeof navigator !== 'undefined' && 'geolocation' in navigator

// Shared reactive state (can be displayed in any view).
export const gpsTrackingEnabled = ref(false)
export const gpsTrackingLastSentAt = ref<string | null>(null)
export const gpsTrackingError = ref<string | null>(null)

/**
 * While the driver has an order in transit, send GPS to POST /drivers/location
 * on an interval (use case: Send GPS included with active navigation).
 */
export function useDriverLocationTracking() {
  const auth = useAuthStore()
  let timer: ReturnType<typeof setInterval> | null = null

  async function fetchInTransitOrderId(): Promise<number | null> {
    try {
      const res = await ordersApi.list({ status: 'in_transit', per_page: 5 })
      const rows = res.data?.data?.data ?? []
      const row = rows[0]
      return row?.id ?? null
    } catch {
      return null
    }
  }

  async function pushLocation(orderId: number) {
    if (!hasGeoSupport) {
      gpsTrackingEnabled.value = false
      gpsTrackingError.value = 'Geolocation not supported'
      return
    }
    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const { latitude: lat, longitude: lng, heading, speed, accuracy } = pos.coords
        const speedMs = speed != null ? speed : null
        const speedKmh = speedMs != null && !Number.isNaN(speedMs) ? Math.round(speedMs * 3.6 * 100) / 100 : undefined
        try {
          await driversApi.updateLocation({
            lat,
            lng,
            delivery_order_id: orderId,
            heading: heading != null ? Math.round(heading) : undefined,
            accuracy: accuracy != null ? Math.round(accuracy) : undefined,
            speed_kmh: speedKmh,
          })
          gpsTrackingEnabled.value = true
          gpsTrackingError.value = null
          gpsTrackingLastSentAt.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
        } catch {
          /* avoid UI noise — network/offline while driving */
          gpsTrackingEnabled.value = false
          gpsTrackingError.value = 'Failed to send GPS'
        }
      },
      () => {
        gpsTrackingEnabled.value = false
        gpsTrackingError.value = 'Location permission denied/unavailable'
      },
      { enableHighAccuracy: true, maximumAge: 10_000, timeout: 15_000 }
    )
  }

  async function tick() {
    if (!auth.isAuthenticated || !auth.isDriver) return
    const oid = await fetchInTransitOrderId()
    if (oid) await pushLocation(oid)
    else gpsTrackingEnabled.value = false
  }

  function start() {
    if (timer || !auth.isDriver) return
    void tick()
    timer = setInterval(() => void tick(), TICK_MS)
  }

  function stop() {
    if (timer) {
      clearInterval(timer)
      timer = null
    }
    gpsTrackingEnabled.value = false
  }

  watch(
    () => [auth.isAuthenticated, auth.isDriver] as const,
    ([loggedIn, driver]) => {
      if (loggedIn && driver) start()
      else stop()
    },
    { immediate: true }
  )

  onMounted(() => {
    if (auth.isAuthenticated && auth.isDriver) start()
  })

  onUnmounted(stop)
}
