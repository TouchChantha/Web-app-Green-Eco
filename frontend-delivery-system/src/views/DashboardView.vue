<template>
  <div class="space-y-5">

    <!-- ── Top bar: greeting + refresh ───────────────────────────────── -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-800">Dashboard</h2>
        <p class="text-sm text-slate-500 mt-0.5">Good {{ greeting }}, {{ auth.user?.name?.split(' ')[0] }} — here's today's overview</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-xs text-slate-400">Last updated: {{ lastUpdated }}</span>
        <button @click="load" :disabled="loading"
          class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-medium hover:bg-slate-50 transition-colors disabled:opacity-50">
          <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
      </div>
    </div>

    <LoadingSpinner v-if="loading && !data" text="Loading dashboard..." :fullPage="true" />

    <template v-if="data">

      <!-- ── KPI Cards Row 1: Orders ─────────────────────────────────── -->
      <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Today's Orders</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total orders today</p>
            <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ data.orders.today ?? data.orders.total }}</p>
          </div>
          <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">In transit</p>
            <p class="text-3xl font-extrabold text-purple-600 mt-1">{{ data.orders.in_transit }}</p>
          </div>
          <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Delivered</p>
            <p class="text-3xl font-extrabold text-[#3d9a3d] mt-1">{{ data.orders.delivered }}</p>
          </div>
          <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Delayed</p>
            <p class="text-3xl font-extrabold text-red-500 mt-1">{{ data.kpis.delayed_orders }}</p>
          </div>
        </div>
      </div>

      <!-- ── KPI Cards Row 2: Performance ───────────────────────────── -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
          <p class="text-xs text-slate-500 font-medium">On-time rate</p>
          <p class="text-3xl font-extrabold text-[#3d9a3d] mt-1">{{ formatPct(data.kpis.on_time_rate) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
          <p class="text-xs text-slate-500 font-medium">Avg delivery</p>
          <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ formatDuration(data.kpis.avg_delivery_time) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
          <p class="text-xs text-slate-500 font-medium">Active drivers</p>
          <p class="text-3xl font-extrabold text-blue-600 mt-1">{{ data.drivers.on_delivery }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
          <p class="text-xs text-slate-500 font-medium">Available</p>
          <p class="text-3xl font-extrabold text-[#3d9a3d] mt-1">{{ data.drivers.available }}</p>
        </div>
      </div>

      <!-- ── Map placeholder + Recent Orders ────────────────────────── -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <!-- Map area -->
        <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
          <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
              <MapIcon class="w-4 h-4 text-[#3d9a3d]" />
              Live Driver Map
            </h3>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span> On delivery
              </div>
              <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-[#3d9a3d]"></span> Available
              </div>
              <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> Delayed
              </div>
              <RouterLink to="/live-tracking"
                class="text-xs text-[#3d9a3d] hover:underline font-medium">
                Full map →
              </RouterLink>
            </div>
          </div>
          <!-- Map placeholder -->
          <div class="relative bg-slate-50 h-64 flex items-center justify-center overflow-hidden">
            <!-- Grid lines -->
            <div class="absolute inset-0 opacity-30"
              style="background-image: linear-gradient(#e2e8f0 1px, transparent 1px), linear-gradient(90deg, #e2e8f0 1px, transparent 1px); background-size: 40px 40px;">
            </div>
            <!-- Simulated driver dots -->
            <div class="absolute top-1/3 left-1/4 w-4 h-4 rounded-full bg-blue-500 shadow-lg border-2 border-white flex items-center justify-center">
              <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
            </div>
            <div class="absolute top-1/2 left-1/2 w-4 h-4 rounded-full bg-red-500 shadow-lg border-2 border-white flex items-center justify-center">
              <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
            </div>
            <div class="absolute bottom-1/3 right-1/3 w-4 h-4 rounded-full bg-blue-500 shadow-lg border-2 border-white flex items-center justify-center">
              <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
            </div>
            <div class="absolute top-1/4 right-1/4 w-4 h-4 rounded-full bg-[#3d9a3d] shadow-lg border-2 border-white flex items-center justify-center">
              <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
            </div>
            <div class="text-center z-10">
              <MapIcon class="w-8 h-8 text-slate-300 mx-auto mb-2" />
              <p class="text-sm text-slate-400 font-medium">Google Map — live driver dots</p>
              <RouterLink to="/live-tracking"
                class="mt-2 inline-flex items-center gap-1 text-xs text-[#3d9a3d] hover:underline">
                Open live tracking →
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Driver status sidebar -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
          <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <TruckIcon class="w-4 h-4 text-[#3d9a3d]" />
            Driver Status
          </h3>
          <div class="space-y-3">
            <div v-for="ds in driverStatuses" :key="ds.label" class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :class="ds.dot"></span>
                <span class="text-sm text-slate-600">{{ ds.label }}</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-24 bg-slate-100 rounded-full h-1.5">
                  <div class="h-1.5 rounded-full transition-all" :class="ds.bar"
                    :style="{ width: driverPct(ds.count) }"></div>
                </div>
                <span class="text-sm font-bold text-slate-700 w-5 text-right">{{ ds.count }}</span>
              </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
              <span>Total Drivers</span>
              <span class="font-bold text-slate-700">{{ data.drivers.total }}</span>
            </div>
          </div>

          <!-- Quick actions -->
          <div class="mt-5 pt-4 border-t border-slate-100 space-y-2">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2">Quick Actions</p>
            <RouterLink to="/orders/create"
              class="flex items-center gap-2 w-full px-3 py-2.5 bg-[#3d9a3d] text-white rounded-xl text-xs font-semibold hover:bg-[#1a4731] transition-colors">
              <PlusIcon class="w-3.5 h-3.5" />
              New Order
            </RouterLink>
            <RouterLink to="/drivers/create"
              class="flex items-center gap-2 w-full px-3 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium hover:bg-slate-200 transition-colors">
              <UserPlusIcon class="w-3.5 h-3.5" />
              Add Driver
            </RouterLink>
            <RouterLink to="/live-tracking"
              class="flex items-center gap-2 w-full px-3 py-2.5 bg-blue-50 text-blue-700 rounded-xl text-xs font-medium hover:bg-blue-100 transition-colors">
              <MapIcon class="w-3.5 h-3.5" />
              Live Tracking
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- ── Recent Orders Table ─────────────────────────────────────── -->
      <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <ClipboardDocumentListIcon class="w-4 h-4 text-[#3d9a3d]" />
            Recent Orders
          </h3>
          <RouterLink to="/orders" class="text-xs text-[#3d9a3d] hover:underline font-medium">
            View all →
          </RouterLink>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Order #</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Recipient</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Driver</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Priority</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-if="recentOrders.length === 0">
                <td colspan="6" class="text-center py-10 text-slate-400 text-sm">No recent orders</td>
              </tr>
              <tr v-for="order in recentOrders" :key="order.id"
                class="hover:bg-slate-50 transition-colors cursor-pointer"
                @click="router.push(`/orders/${order.id}`)">
                <td class="px-5 py-3.5">
                  <span class="font-mono text-xs font-bold text-[#3d9a3d]">{{ order.order_number }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <p class="font-medium text-slate-800">{{ order.recipient_name }}</p>
                </td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                  <span v-if="order.driver" class="text-slate-600">{{ order.driver.user?.name }}</span>
                  <span v-else class="text-slate-400 text-xs italic">—</span>
                </td>
                <td class="px-5 py-3.5">
                  <StatusBadge :status="order.status" />
                </td>
                <td class="px-5 py-3.5 hidden lg:table-cell">
                  <PriorityBadge :priority="order.priority" />
                </td>
                <td class="px-5 py-3.5 text-right" @click.stop>
                  <RouterLink :to="`/orders/${order.id}`"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-[#3d9a3d] bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <EyeIcon class="w-3.5 h-3.5" />
                    View
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Footer -->
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
          <span class="text-xs text-slate-400">Auto-refresh every 60s</span>
        </div>
      </div>

    </template>

    <div v-else-if="error && !loading" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
      <p class="text-red-600">{{ error }}</p>
      <button @click="load" class="mt-3 text-sm text-red-700 underline">Retry</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrdersStore } from '../stores/orders'
import { reportsApi } from '../api/reports'
import StatusBadge from '../components/ui/StatusBadge.vue'
import PriorityBadge from '../components/ui/PriorityBadge.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'
import {
  TruckIcon, MapIcon, ClipboardDocumentListIcon,
  ArrowPathIcon, PlusIcon, UserPlusIcon, EyeIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const ordersStore = useOrdersStore()
const router = useRouter()

const data = ref<any>(null)
const loading = ref(false)
const error = ref('')
const lastUpdated = ref('—')
let refreshTimer: ReturnType<typeof setInterval> | null = null

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'morning'
  if (h < 17) return 'afternoon'
  return 'evening'
})

const driverStatuses = computed(() => [
  { label: 'Available',    count: data.value?.drivers.available   ?? 0, dot: 'bg-[#3d9a3d]', bar: 'bg-[#3d9a3d]' },
  { label: 'On Delivery',  count: data.value?.drivers.on_delivery ?? 0, dot: 'bg-blue-500',   bar: 'bg-blue-500' },
  { label: 'Offline',      count: data.value?.drivers.offline     ?? 0, dot: 'bg-slate-400',  bar: 'bg-slate-400' },
])

const recentOrders = computed(() => ordersStore.orders.slice(0, 5))

function driverPct(n: number) {
  const total = data.value?.drivers?.total || 1
  return `${Math.round((n / total) * 100)}%`
}

function formatPct(v: number | null) {
  if (v == null) return '0%'
  return `${Math.round(v)}%`
}

function formatDuration(mins: number | null) {
  if (!mins) return 'N/A'
  if (mins < 60) return `${Math.round(mins)}m`
  return `${Math.floor(mins / 60)}h ${Math.round(mins % 60)}m`
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const [dashRes] = await Promise.all([
      reportsApi.dashboard(),
      ordersStore.fetchOrders({ page: 1, per_page: 5 }),
    ])
    data.value = dashRes.data.data
    lastUpdated.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to load dashboard'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  load()
  // Auto-refresh every 60s
  refreshTimer = setInterval(load, 60_000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
})
</script>
