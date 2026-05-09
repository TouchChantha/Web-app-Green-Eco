<template>
  <div class="space-y-5">
    <RouterLink to="/drivers" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#3d9a3d] transition-colors">
      <ArrowLeftIcon class="w-4 h-4" />
      Back to Drivers
    </RouterLink>

    <LoadingSpinner v-if="store.loading" text="Loading driver..." :fullPage="true" />

    <template v-else-if="driver">

      <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 items-start">

        <!-- ── Left: Driver Profile ────────────────────────────────────── -->
        <div class="space-y-4">

          <!-- Profile card -->
          <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
              <h2 class="text-base font-semibold text-slate-800">Driver detail</h2>
              <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-semibold">Admin</span>
            </div>

            <div class="p-6">
              <!-- Avatar + info -->
              <div class="flex items-center gap-4 mb-5">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#3d9a3d] to-[#1a4731] flex items-center justify-center text-white text-2xl font-bold shrink-0">
                  {{ driver.user?.name?.slice(0, 2).toUpperCase() }}
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-800">{{ driver.user?.name }}</h3>
                  <p class="text-sm text-slate-500">{{ driver.vehicle_plate }} · {{ driver.vehicle_type }} · {{ driver.license_number }}</p>
                  <div class="flex items-center gap-1.5 mt-1">
                    <span class="w-2 h-2 rounded-full"
                      :class="driver.status === 'available' ? 'bg-[#3d9a3d]' : driver.status === 'on_delivery' ? 'bg-amber-400' : 'bg-slate-400'">
                    </span>
                    <span class="text-sm font-medium capitalize"
                      :class="driver.status === 'available' ? 'text-[#3d9a3d]' : driver.status === 'on_delivery' ? 'text-amber-600' : 'text-slate-500'">
                      {{ driver.status.replace('_', ' ') }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Contact info -->
              <div class="space-y-2 text-sm mb-5">
                <div class="flex items-center gap-2 text-slate-600">
                  <EnvelopeIcon class="w-4 h-4 text-slate-400 shrink-0" />
                  {{ driver.user?.email }}
                </div>
                <div v-if="driver.user?.phone" class="flex items-center gap-2 text-slate-600">
                  <PhoneIcon class="w-4 h-4 text-slate-400 shrink-0" />
                  {{ driver.user.phone }}
                </div>
              </div>

              <!-- Status update -->
              <div class="pt-4 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2">Update Status</p>
                <div class="flex gap-2">
                  <button v-for="s in ['available', 'on_delivery', 'offline']" :key="s"
                    @click="changeStatus(s)"
                    :disabled="driver.status === s || updatingStatus"
                    :class="[
                      'flex-1 px-3 py-2 rounded-xl text-xs font-semibold transition-all disabled:opacity-50',
                      driver.status === s
                        ? 'bg-[#3d9a3d] text-white shadow-sm'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                    ]">
                    {{ s.replace('_', ' ') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Vehicle details -->
          <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
              <TruckIcon class="w-4 h-4 text-[#3d9a3d]" />
              Vehicle Details
            </h3>
            <div class="grid grid-cols-3 gap-3 text-sm">
              <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                <p class="text-xs text-slate-500">Plate</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ driver.vehicle_plate }}</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                <p class="text-xs text-slate-500">Type</p>
                <p class="font-bold text-slate-800 mt-0.5 capitalize">{{ driver.vehicle_type }}</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                <p class="text-xs text-slate-500">License</p>
                <p class="font-bold text-slate-800 mt-0.5 font-mono text-xs">{{ driver.license_number }}</p>
              </div>
            </div>
          </div>

          <!-- Recent orders -->
          <div v-if="driver.orders?.length" class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <ClipboardDocumentListIcon class="w-4 h-4 text-[#3d9a3d]" />
                Recent Orders
              </h3>
              <span class="text-xs text-slate-400">Last {{ driver.orders.length }}</span>
            </div>
            <div class="divide-y divide-slate-100">
              <div v-for="order in driver.orders.slice(0, 5)" :key="order.id"
                class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 cursor-pointer transition-colors"
                @click="router.push(`/orders/${order.id}`)">
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-mono font-bold text-[#3d9a3d]">{{ order.order_number }}</p>
                  <p class="text-sm text-slate-700 truncate">{{ order.delivery_address }}</p>
                </div>
                <StatusBadge :status="order.status" />
                <span class="text-xs text-slate-400 shrink-0">{{ formatDate(order.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Right: Performance KPIs + Chart ────────────────────────── -->
        <div class="space-y-4">

          <!-- KPI cards -->
          <div v-if="performance" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
              <h2 class="text-base font-semibold text-slate-800">Driver detail</h2>
              <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-semibold">Admin</span>
            </div>
            <div class="p-6">
              <!-- Mini profile repeat for right card -->
              <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#3d9a3d] to-[#1a4731] flex items-center justify-center text-white text-lg font-bold shrink-0">
                  {{ driver.user?.name?.slice(0, 2).toUpperCase() }}
                </div>
                <div>
                  <p class="font-bold text-slate-800">{{ driver.user?.name }}</p>
                  <p class="text-xs text-slate-400">{{ driver.vehicle_plate }} · {{ driver.vehicle_type }} · {{ driver.license_number }}</p>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#3d9a3d]"></span>
                    <span class="text-xs text-[#3d9a3d] font-medium capitalize">{{ driver.status.replace('_', ' ') }}</span>
                  </div>
                </div>
              </div>

              <!-- KPI row -->
              <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-200">
                  <p class="text-xs text-slate-500 mb-1">On-time rate</p>
                  <p class="text-2xl font-extrabold text-[#3d9a3d]">{{ Math.round(performance.on_time_rate || 0) }}%</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-200">
                  <p class="text-xs text-slate-500 mb-1">Completion</p>
                  <p class="text-2xl font-extrabold text-blue-600">
                    {{ performance.total_orders > 0 ? Math.round((performance.completed / performance.total_orders) * 100) : 0 }}%
                  </p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-200">
                  <p class="text-xs text-slate-500 mb-1">Avg time</p>
                  <p class="text-2xl font-extrabold text-slate-800">{{ formatDuration(performance.avg_delivery_time) }}</p>
                </div>
              </div>

              <!-- Delivery history chart (last 7 days) -->
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Delivery history (last 7 days)</p>
                <div class="flex items-end gap-1.5 h-20">
                  <div v-for="(bar, i) in deliveryBars" :key="i"
                    class="flex-1 rounded-t-md transition-all hover:opacity-80 cursor-default relative group"
                    :class="i === 4 ? 'bg-[#3d9a3d]' : 'bg-slate-200'"
                    :style="{ height: `${bar.pct}%`, minHeight: '8px' }">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none z-10">
                      {{ bar.count }}
                    </div>
                  </div>
                </div>
                <div class="flex gap-1.5 mt-1">
                  <div v-for="(bar, i) in deliveryBars" :key="i" class="flex-1 text-center">
                    <span class="text-[9px] text-slate-400">{{ bar.day }}</span>
                  </div>
                </div>
              </div>

              <!-- API endpoints -->
              <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-slate-100">
              </div>
            </div>
          </div>

          <!-- Full performance stats -->
          <div v-if="performance" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
              <ChartBarIcon class="w-4 h-4 text-[#3d9a3d]" />
              Performance Summary (Last 30 Days)
            </h3>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                <p class="text-xs text-slate-500">Total Orders</p>
                <p class="text-xl font-bold text-slate-800 mt-0.5">{{ performance.total_orders }}</p>
              </div>
              <div class="bg-green-50 rounded-xl p-3 border border-green-200">
                <p class="text-xs text-green-600">Completed</p>
                <p class="text-xl font-bold text-green-700 mt-0.5">{{ performance.completed }}</p>
              </div>
              <div class="bg-red-50 rounded-xl p-3 border border-red-200">
                <p class="text-xs text-red-500">Failed</p>
                <p class="text-xl font-bold text-red-700 mt-0.5">{{ performance.failed }}</p>
              </div>
              <div class="bg-amber-50 rounded-xl p-3 border border-amber-200">
                <p class="text-xs text-amber-600">Total Distance</p>
                <p class="text-xl font-bold text-amber-700 mt-0.5">{{ Math.round(performance.total_distance_km || 0) }} km</p>
              </div>
            </div>
          </div>

          <!-- Location -->
          <div v-if="driver.current_lat" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
              <MapPinIcon class="w-4 h-4 text-[#3d9a3d]" />
              Last Known Location
            </h3>
            <p class="text-sm font-mono text-slate-600">{{ driver.current_lat }}, {{ driver.current_lng }}</p>
            <p class="text-xs text-slate-400 mt-1">Updated: {{ formatDateTime(driver.last_location_at) }}</p>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useDriversStore } from '../../stores/drivers'
import { reportsApi } from '../../api/reports'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import LoadingSpinner from '../../components/ui/LoadingSpinner.vue'
import {
  ArrowLeftIcon, TruckIcon, ChartBarIcon, ClipboardDocumentListIcon,
  EnvelopeIcon, PhoneIcon, MapPinIcon,
} from '@heroicons/vue/24/outline'

const store = useDriversStore()
const route = useRoute()
const router = useRouter()
const driver = ref<any>(null)
const performance = ref<any>(null)
const updatingStatus = ref(false)

// Simulated 7-day delivery bars (replace with real data when available)
const deliveryBars = computed(() => {
  const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  const counts = performance.value
    ? [4, 6, 3, 7, 9, 5, 2]  // placeholder — replace with real daily data
    : [0, 0, 0, 0, 0, 0, 0]
  const max = Math.max(...counts, 1)
  return days.map((day, i) => ({
    day,
    count: counts[i],
    pct: Math.round((counts[i] / max) * 100),
  }))
})

async function load() {
  await store.fetchDriver(Number(route.params.id))
  driver.value = store.currentDriver
}

async function loadPerformance() {
  try {
    const res = await reportsApi.driverPerformance(Number(route.params.id))
    performance.value = res.data.data.summary
  } catch {}
}

async function changeStatus(status: string) {
  updatingStatus.value = true
  try {
    await store.updateStatus(Number(route.params.id), status)
    await load()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to update status')
  } finally {
    updatingStatus.value = false
  }
}

function formatDateTime(d: string) {
  if (!d) return 'N/A'
  return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function formatDuration(mins: number) {
  if (!mins) return 'N/A'
  if (mins < 60) return `${Math.round(mins)}m`
  return `${Math.floor(mins / 60)}h ${Math.round(mins % 60)}m`
}

onMounted(async () => {
  await load()
  loadPerformance()
})
</script>
