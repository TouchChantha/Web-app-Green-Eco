<template>
  <div class="space-y-5">

    <!-- ── Top two-column layout: Driver Home + Active Delivery ───────── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

      <!-- ── Driver Home ─────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Card header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-slate-800">Driver home</h2>
          <span class="text-xs bg-green-100 text-[#3d9a3d] px-2.5 py-1 rounded-full font-semibold">Driver</span>
        </div>

        <div class="p-6 space-y-5">
          <!-- Greeting + status toggle -->
          <div class="flex items-center justify-between gap-4">
            <div>
              <h3 class="text-lg font-bold text-slate-800">
                Good {{ greeting }}, {{ firstName }} 👋
              </h3>
            </div>
            <!-- Online / Offline toggle -->
            <div class="flex items-center gap-2 shrink-0">
              <span class="text-xs text-slate-500 font-medium">Status:</span>
              <div class="flex rounded-xl overflow-hidden border border-slate-300 text-xs font-semibold">
                <button
                  @click="setStatus('available')"
                  :disabled="updatingStatus"
                  :class="[
                    'px-3 py-1.5 transition-colors',
                    myDriver?.status === 'available'
                      ? 'bg-[#0d3320] text-white'
                      : 'bg-white text-slate-500 hover:bg-slate-50'
                  ]">
                  Online
                </button>
                <button
                  @click="setStatus('offline')"
                  :disabled="updatingStatus"
                  :class="[
                    'px-3 py-1.5 transition-colors border-l border-slate-300',
                    myDriver?.status === 'offline'
                      ? 'bg-slate-700 text-white'
                      : 'bg-white text-slate-500 hover:bg-slate-50'
                  ]">
                  Offline
                </button>
              </div>
            </div>
          </div>

          <!-- Stats row -->
          <div class="grid grid-cols-3 gap-3">
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-center">
              <p class="text-xs text-slate-500 leading-tight">Today's orders</p>
              <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ stats.total }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-center">
              <p class="text-xs text-slate-500 leading-tight">Completed</p>
              <p class="text-2xl font-extrabold text-[#3d9a3d] mt-1">{{ stats.delivered }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-center">
              <p class="text-xs text-slate-500 leading-tight">Pending</p>
              <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ stats.assigned }}</p>
            </div>
          </div>

          <!-- My orders list -->
          <div>
            <p class="text-sm font-semibold text-slate-700 mb-3">My orders</p>
            <LoadingSpinner v-if="loading" text="Loading..." />
            <div v-else-if="allOrders.length === 0" class="py-6 text-center text-slate-400 text-sm">
              No orders assigned yet
            </div>
            <div v-else class="space-y-2">
              <div v-for="order in allOrders.slice(0, 4)" :key="order.id"
                class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0">
                <!-- Order number + address -->
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-mono font-bold text-slate-700">{{ order.order_number }}</p>
                  <p class="text-xs text-slate-400 truncate mt-0.5">
                    {{ order.recipient_name }} · {{ order.delivery_address?.split(',')[0] }}
                  </p>
                </div>
                <!-- Status badge -->
                <StatusBadge :status="order.status" />
                <!-- Action button -->
                <div class="shrink-0">
                  <button v-if="order.status === 'in_transit'"
                    type="button"
                    @click.stop="openMaps(order.delivery_address)"
                    class="px-3 py-1.5 bg-[#0d3320] text-white text-xs font-semibold rounded-lg hover:bg-[#1a4731] transition-colors">
                    Maps
                  </button>
                  <button v-else-if="order.status === 'assigned'"
                    @click="router.push(`/orders/${order.id}`)"
                    class="px-3 py-1.5 border border-slate-300 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition-colors">
                    View
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- API badges -->
          <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
          </div>
        </div>
      </div>

      <!-- ── Active Delivery / Navigation ───────────────────────────── -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-slate-800">Active delivery / navigation</h2>
          <div class="flex items-center gap-2">
            <span
              v-if="activeOrder?.status === 'in_transit'"
              :class="[
                'inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full font-semibold border',
                gpsTrackingEnabled
                  ? 'bg-green-50 text-green-700 border-green-200'
                  : 'bg-red-50 text-red-600 border-red-200'
              ]"
            >
              <span class="relative inline-flex h-2.5 w-2.5">
                <span
                  v-if="gpsTrackingEnabled"
                  class="absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75 animate-ping"
                />
                <span
                  :class="[
                    'relative inline-flex rounded-full h-2.5 w-2.5',
                    gpsTrackingEnabled ? 'bg-green-500' : 'bg-red-500'
                  ]"
                />
              </span>
              Live GPS: {{ gpsTrackingEnabled ? 'ON' : 'OFF' }}
            </span>
            <span class="text-xs bg-green-100 text-[#3d9a3d] px-2.5 py-1 rounded-full font-semibold">Driver</span>
          </div>
        </div>

        <div class="p-6 space-y-4">
          <template v-if="activeOrder">
            <!-- Navigation map placeholder -->
            <div class="relative bg-[#eef0eb] rounded-xl overflow-hidden h-40 border border-slate-200">
              <div class="absolute inset-0 opacity-20"
                style="background-image: linear-gradient(#c8cfc0 1px, transparent 1px), linear-gradient(90deg, #c8cfc0 1px, transparent 1px); background-size: 36px 36px;">
              </div>
              <!-- Road lines -->
              <svg class="absolute inset-0 w-full h-full" style="pointer-events:none;">
                <line x1="0" y1="50%" x2="100%" y2="50%" stroke="#b0b8a8" stroke-width="2.5" opacity="0.5"/>
                <line x1="40%" y1="0" x2="40%" y2="100%" stroke="#b0b8a8" stroke-width="2" opacity="0.4"/>
                <!-- Route line -->
                <line x1="30%" y1="65%" x2="72%" y2="28%" stroke="#3d9a3d" stroke-width="2" stroke-dasharray="6,4" opacity="0.7"/>
              </svg>
              <!-- Driver dot -->
              <div class="absolute" style="left:30%; top:65%; transform:translate(-50%,-50%)">
                <div class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-md"></div>
              </div>
              <!-- Destination dot -->
              <div class="absolute" style="left:72%; top:28%; transform:translate(-50%,-50%)">
                <div class="w-4 h-4 rounded-full bg-red-500 border-2 border-white shadow-md"></div>
              </div>
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <span class="text-xs text-slate-500 bg-white/70 px-2 py-1 rounded">Google Maps Navigation</span>
              </div>
            </div>

            <!-- Delivery info -->
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-bold text-slate-800">Delivering to {{ activeOrder.recipient_name }}</p>
                <p class="text-sm text-slate-500 mt-0.5">{{ activeOrder.delivery_address }}</p>
              </div>
              <span v-if="activeOrder.route?.estimated_duration" class="text-sm font-bold text-blue-600 shrink-0">
                {{ Math.round(activeOrder.route.estimated_duration) }} min
              </span>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap gap-2">
              <a v-if="callHref" :href="callHref"
                class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                <PhoneIcon class="w-4 h-4" />
                Call
              </a>
              <button v-else disabled
                class="flex items-center gap-2 px-4 py-2.5 border border-slate-200 text-slate-400 rounded-xl text-sm font-medium cursor-not-allowed">
                <PhoneIcon class="w-4 h-4" />
                Call
              </button>
              <button type="button" @click="openMaps(activeOrder.delivery_address)"
                class="flex items-center gap-2 px-4 py-2.5 border border-[#3d9a3d] text-[#0d3320] rounded-xl text-sm font-semibold hover:bg-green-50 transition-colors">
                <MapPinIcon class="w-4 h-4" />
                Navigate
              </button>
              <button
                v-if="activeOrder.status === 'assigned'"
                @click="startDelivery(activeOrder.id)"
                :disabled="actionLoading === activeOrder.id"
                class="flex items-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 transition-colors disabled:opacity-60 flex-1 justify-center">
                <TruckIcon class="w-4 h-4" />
                Start delivery
              </button>
              <!-- Mark delivered -->
              <button
                v-if="activeOrder.status === 'in_transit'"
                @click="markDelivered(activeOrder.id)"
                :disabled="actionLoading === activeOrder.id"
                class="flex items-center gap-2 px-4 py-2.5 bg-[#0d3320] text-white rounded-xl text-sm font-semibold hover:bg-[#1a4731] transition-colors disabled:opacity-60 flex-1 justify-center">
                <CheckCircleIcon class="w-4 h-4" />
                ✓ Mark delivered
              </button>
              <!-- Report fail -->
              <button
                v-if="activeOrder.status === 'in_transit'"
                @click="openFailModal(activeOrder.id)"
                class="flex items-center gap-2 px-4 py-2.5 border border-red-200 text-red-600 bg-red-50 rounded-xl text-sm font-medium hover:bg-red-100 transition-colors">
                <XCircleIcon class="w-4 h-4" />
                × Report fail
              </button>
            </div>

            <!-- API note -->
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs text-slate-500 space-y-1">
              <p>"Mark delivered" → POST /orders/{'{id}'}/status {'{status: "delivered"}'}</p>
              <p v-if="activeOrder.status === 'in_transit'" class="flex items-center gap-1.5">
                <span class="relative inline-flex h-2 w-2">
                  <span class="absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-70 animate-ping"></span>
                  <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                </span>
                Live tracking sends GPS every 30s while delivery is in transit
              </p>
              <p v-if="activeOrder.status === 'in_transit' && gpsTrackingLastSentAt" class="text-[11px] text-slate-400">
                Last GPS update: {{ gpsTrackingLastSentAt }}
              </p>
              <p v-if="activeOrder.status === 'in_transit' && gpsTrackingError" class="text-[11px] text-red-500 font-medium">
                GPS issue: {{ gpsTrackingError }}
              </p>
            </div>

            <!-- API badges -->
            <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-100">
            </div>
          </template>

          <!-- No active delivery -->
          <template v-else>
            <div class="relative bg-[#eef0eb] rounded-xl overflow-hidden h-40 border border-slate-200 flex items-center justify-center">
              <div class="absolute inset-0 opacity-20"
                style="background-image: linear-gradient(#c8cfc0 1px, transparent 1px), linear-gradient(90deg, #c8cfc0 1px, transparent 1px); background-size: 36px 36px;">
              </div>
              <p class="text-sm text-slate-400 z-10">Google Maps Navigation</p>
            </div>
            <div class="py-4 text-center">
              <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <TruckIcon class="w-6 h-6 text-slate-400" />
              </div>
              <p class="font-medium text-slate-600">No active delivery</p>
              <p class="text-sm text-slate-400 mt-1">You'll see navigation here when on delivery</p>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- ── My Performance ──────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">My performance</h2>
        <span class="text-xs bg-green-100 text-[#3d9a3d] px-2.5 py-1 rounded-full font-semibold">Driver</span>
      </div>

      <div class="p-6 space-y-5">
        <!-- This month header -->
        <p class="text-sm font-semibold text-slate-700">This month — {{ auth.user?.name }}</p>

        <!-- KPI cards -->
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-center">
            <p class="text-xs text-slate-500 mb-1">Score</p>
            <p class="text-3xl font-extrabold text-[#3d9a3d]">{{ kpiScore }}</p>
          </div>
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-center">
            <p class="text-xs text-slate-500 mb-1">On-time</p>
            <p class="text-3xl font-extrabold text-slate-800">{{ kpiOnTime }}</p>
          </div>
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-center">
            <p class="text-xs text-slate-500 mb-1">Completion</p>
            <p class="text-3xl font-extrabold text-slate-800">{{ kpiCompletion }}</p>
          </div>
        </div>

        <!-- Deliveries this week bar chart -->
        <div>
          <p class="text-sm font-semibold text-slate-700 mb-3">Deliveries this week</p>
          <div class="flex items-end gap-1.5 h-16">
            <div v-for="(bar, i) in weekBars" :key="i"
              class="flex-1 rounded-t-md transition-all hover:opacity-80 cursor-default relative group"
              :class="i === todayIndex ? 'bg-[#3d9a3d]' : 'bg-[#c8e6c9]'"
              :style="{ height: `${bar.pct}%`, minHeight: '6px' }">
              <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none z-10">
                {{ bar.count }}
              </div>
            </div>
          </div>
          <div class="flex gap-1.5 mt-1">
            <div v-for="(bar, i) in weekBars" :key="i" class="flex-1 text-center">
              <span class="text-[9px] text-slate-400">{{ bar.day }}</span>
            </div>
          </div>
        </div>

        <!-- Recent deliveries -->
        <div>
          <p class="text-sm font-semibold text-slate-700 mb-3">Recent deliveries</p>
          <div class="space-y-0 divide-y divide-slate-100">
            <div v-for="order in recentDeliveries" :key="order.id"
              class="flex items-center gap-3 py-3 hover:bg-slate-50 cursor-pointer transition-colors rounded-lg px-2"
              @click="router.push(`/orders/${order.id}`)">
              <span class="font-mono text-xs font-bold text-slate-700 w-20 shrink-0">{{ order.order_number }}</span>
              <span class="flex-1 text-sm text-slate-500">
                {{ order.actual_duration ? `${Math.round(order.actual_duration)} min` : '—' }}
              </span>
              <span :class="[
                'text-xs font-semibold',
                order.status === 'delivered' ? 'text-[#3d9a3d]' : 'text-red-500'
              ]">
                {{ order.status === 'delivered' ? 'On time' : 'Late' }}
              </span>
            </div>
            <div v-if="recentDeliveries.length === 0" class="py-4 text-center text-sm text-slate-400">
              No recent deliveries
            </div>
          </div>
        </div>

        <!-- API badge -->
        <div class="pt-2 border-t border-slate-100">
        </div>
      </div>
    </div>

    <!-- ── Fail Modal ──────────────────────────────────────────────────── -->
    <ConfirmModal
      :show="showFailModal"
      title="Report Delivery Failure"
      message="Please provide a reason for the delivery failure."
      confirm-text="Mark Failed"
      variant="warning"
      :has-input="true"
      input-placeholder="Reason for failure (required)"
      @confirm="markFailed"
      @cancel="showFailModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrdersStore } from '../stores/orders'
import { driversApi } from '../api/drivers'
import { authApi } from '../api/auth'
import { reportsApi } from '../api/reports'
import { openGoogleMapsDirections, phoneDialHref } from '../utils/maps'
import {
  gpsTrackingEnabled,
  gpsTrackingLastSentAt,
  gpsTrackingError,
} from '../composables/useDriverLocationTracking'
import StatusBadge from '../components/ui/StatusBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import ConfirmModal from '../components/ui/ConfirmModal.vue'
import {
  TruckIcon, CheckCircleIcon, XCircleIcon, PhoneIcon, MapPinIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const store = useOrdersStore()
const router = useRouter()

const myDriver = ref<any>(null)
const allOrders = ref<any[]>([])
const loading = ref(false)
const actionLoading = ref<number | null>(null)
const updatingStatus = ref(false)
const showFailModal = ref(false)
const failOrderId = ref<number | null>(null)
const performance = ref<any>(null)
const performanceDaily = ref<any[]>([])

// ── Computed ───────────────────────────────────────────────────────────────
const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'morning'
  if (h < 17) return 'afternoon'
  return 'evening'
})

const firstName = computed(() => auth.user?.name?.split(' ')[0] || 'Driver')

const activeOrder = computed(() =>
  allOrders.value.find(o => ['assigned', 'in_transit'].includes(o.status)) ?? null
)

const callHref = computed(() =>
  activeOrder.value ? phoneDialHref(activeOrder.value.recipient_phone) : null
)

function openMaps(address: string | undefined) {
  if (address?.trim()) openGoogleMapsDirections(address)
}

const kpiScore = computed(() => {
  const d = performanceDaily.value
  if (d.length) {
    const latest = d[d.length - 1]
    if (latest.performance_score != null) return String(Math.round(latest.performance_score))
  }
  if (performance.value?.on_time_rate != null) return String(Math.round(performance.value.on_time_rate))
  return '—'
})

const kpiOnTime = computed(() => {
  if (performance.value?.on_time_rate != null) return `${Math.round(performance.value.on_time_rate)}%`
  return '—'
})

const kpiCompletion = computed(() => {
  const t = performance.value?.total_orders ?? 0
  if (t > 0 && performance.value?.completed != null)
    return `${Math.round((performance.value.completed / t) * 100)}%`
  return '—'
})

function ymd(d: Date) {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

const recentDeliveries = computed(() =>
  allOrders.value.filter(o => ['delivered', 'failed'].includes(o.status)).slice(0, 4)
)

const stats = computed(() => ({
  total:      allOrders.value.length,
  assigned:   allOrders.value.filter(o => o.status === 'assigned').length,
  in_transit: allOrders.value.filter(o => o.status === 'in_transit').length,
  delivered:  allOrders.value.filter(o => o.status === 'delivered').length,
}))

// ── Week bar chart ─────────────────────────────────────────────────────────
const todayIndex = new Date().getDay() === 0 ? 6 : new Date().getDay() - 1
const weekBars = computed(() => {
  const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  const now = new Date()
  const dow = now.getDay()
  const mondayOffset = dow === 0 ? -6 : 1 - dow
  const monday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + mondayOffset)
  const daily = performanceDaily.value || []
  const byDate: Record<string, any> = {}
  for (const r of daily) {
    const raw = r.period_date ?? r.period
    const key =
      typeof raw === 'string'
        ? raw.slice(0, 10)
        : raw
          ? ymd(new Date(raw))
          : ''
    if (key) byDate[key] = r
  }
  const counts = labels.map((_, i) => {
    const d = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + i)
    const rec = byDate[ymd(d)]
    return rec?.completed_orders ?? 0
  })
  const max = Math.max(...counts, 1)
  return labels.map((day, i) => ({
    day,
    count: counts[i],
    pct: Math.round((counts[i] / max) * 100),
  }))
})

// ── Actions ────────────────────────────────────────────────────────────────
async function setStatus(status: string) {
  if (!myDriver.value) return
  updatingStatus.value = true
  try {
    await driversApi.updateStatus(myDriver.value.id, status)
    myDriver.value = { ...myDriver.value, status }
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to update status')
  } finally {
    updatingStatus.value = false
  }
}

async function markDelivered(orderId: number) {
  actionLoading.value = orderId
  try {
    await store.updateStatus(orderId, { status: 'delivered' })
    await loadOrders()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to mark delivered')
  } finally {
    actionLoading.value = null
  }
}

async function startDelivery(orderId: number) {
  actionLoading.value = orderId
  try {
    await store.updateStatus(orderId, { status: 'in_transit' })
    await loadOrders()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to start delivery')
  } finally {
    actionLoading.value = null
  }
}

function openFailModal(orderId: number) {
  failOrderId.value = orderId
  showFailModal.value = true
}

async function markFailed(reason: string) {
  if (!failOrderId.value) return
  actionLoading.value = failOrderId.value
  showFailModal.value = false
  try {
    await store.updateStatus(failOrderId.value, { status: 'failed', delay_reason: reason })
    await loadOrders()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to update status')
  } finally {
    actionLoading.value = null
    failOrderId.value = null
  }
}

async function loadOrders() {
  loading.value = true
  try {
    await store.fetchOrders({ per_page: 50 })
    allOrders.value = store.orders
  } finally {
    loading.value = false
  }
}

async function loadMyDriver() {
  try {
    const res = await authApi.me()
    const user = res.data.data
    if (user.driver) {
      myDriver.value = user.driver
      // Load performance
      try {
        const perfRes = await reportsApi.driverPerformance(user.driver.id)
        performance.value = perfRes.data.data.summary
        performanceDaily.value = perfRes.data.data.daily ?? []
      } catch {}
    }
  } catch {}
}

onMounted(() => Promise.all([loadOrders(), loadMyDriver()]))
</script>
