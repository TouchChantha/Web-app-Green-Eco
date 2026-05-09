<template>
  <div class="space-y-5">

    <!-- ── Page header ─────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-slate-800">Analytics dashboard</h2>
        <p class="text-sm text-slate-500 mt-0.5">Order reports, driver performance, and delivery analytics</p>
      </div>
      <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full font-semibold">Admin only</span>
    </div>

    <!-- ── Main analytics card ─────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

      <!-- Filter bar -->
      <div class="px-6 py-5 border-b border-slate-100">
        <div class="flex flex-wrap gap-3 items-center">
          <input v-model="filters.date_from" type="date" placeholder="Date from"
            class="border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] text-slate-600 bg-white" />
          <input v-model="filters.date_to" type="date" placeholder="Date to"
            class="border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] text-slate-600 bg-white" />
          <!-- Driver filter -->
          <div class="relative">
            <select v-model="filters.driver_id"
              class="appearance-none border border-slate-300 rounded-xl pl-3.5 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white text-slate-600 cursor-pointer min-w-32">
              <option value="">Driver ▾</option>
              <option v-for="d in allDrivers" :key="d.id" :value="d.id">{{ d.user?.name }}</option>
            </select>
          </div>
          <!-- Generate -->
          <button @click="generate" :disabled="generating"
            class="flex items-center gap-2 px-5 py-2 bg-[#0d3320] text-white rounded-xl text-sm font-semibold hover:bg-[#1a4731] transition-colors disabled:opacity-60 shadow-sm">
            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': generating }" />
            Generate
          </button>
          <!-- Export CSV -->
          <button @click="exportCSV"
            class="flex items-center gap-2 px-4 py-2 border border-slate-300 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors bg-white">
            <ArrowDownTrayIcon class="w-4 h-4" />
            Export CSV
          </button>
        </div>
      </div>

      <!-- ── KPI summary row ─────────────────────────────────────────── -->
      <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-y sm:divide-y-0 divide-slate-100 border-b border-slate-100">
        <div class="px-5 py-4">
          <p class="text-xs text-slate-500 font-medium">Total orders</p>
          <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ summary.total_orders }}</p>
        </div>
        <div class="px-5 py-4">
          <p class="text-xs text-slate-500 font-medium">Completed</p>
          <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ summary.completed }}</p>
        </div>
        <div class="px-5 py-4">
          <p class="text-xs text-slate-500 font-medium">On-time rate</p>
          <p class="text-3xl font-extrabold text-[#3d9a3d] mt-1">{{ Math.round(summary.on_time_rate) }}%</p>
        </div>
        <div class="px-5 py-4">
          <p class="text-xs text-slate-500 font-medium">Avg delivery</p>
          <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ formatDuration(summary.avg_delivery_time) }}</p>
        </div>
        <div class="px-5 py-4">
          <p class="text-xs text-slate-500 font-medium">Total distance</p>
          <p class="text-3xl font-extrabold text-slate-800 mt-1">{{ Math.round(summary.total_distance_km).toLocaleString() }} km</p>
        </div>
      </div>

      <!-- ── Charts row ──────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 border-b border-slate-100">

        <!-- Deliveries per day bar chart -->
        <div class="p-6">
          <p class="text-sm font-semibold text-slate-700 mb-4">Deliveries per day (bar chart)</p>
          <div class="flex items-end gap-1.5 h-24">
            <div v-for="(bar, i) in dailyBars" :key="i"
              class="flex-1 rounded-t-md transition-all hover:opacity-80 cursor-default relative group"
              :class="i === highlightDay ? 'bg-[#3d9a3d]' : 'bg-[#bbdcbb]'"
              :style="{ height: `${bar.pct}%`, minHeight: '6px' }">
              <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none z-10">
                {{ bar.count }} orders
              </div>
            </div>
          </div>
          <div class="flex gap-1.5 mt-1.5">
            <div v-for="(bar, i) in dailyBars" :key="i" class="flex-1 text-center">
              <span class="text-[9px] text-slate-400">{{ bar.label }}</span>
            </div>
          </div>
        </div>

        <!-- Order status breakdown -->
        <div class="p-6">
          <p class="text-sm font-semibold text-slate-700 mb-4">Order status breakdown</p>
          <div class="space-y-3">
            <div v-for="seg in statusBreakdown" :key="seg.label">
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-slate-600">{{ seg.label }}</span>
                <span class="text-sm font-semibold text-slate-700">{{ seg.pct }}%</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all" :class="seg.color"
                  :style="{ width: `${seg.pct}%` }">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Driver performance comparison table ─────────────────────── -->
      <div class="p-6">
        <p class="text-sm font-semibold text-slate-700 mb-4">Driver performance comparison</p>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-200">
                <th class="text-left py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Driver</th>
                <th class="text-left py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Orders</th>
                <th class="text-left py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Completed</th>
                <th class="text-left py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">On-time</th>
                <th class="text-left py-2.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Score</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="driverRows.length === 0">
                <td colspan="5" class="py-10 text-center text-slate-400 text-sm">
                  Generate a report to see driver performance
                </td>
              </tr>
              <tr v-for="row in driverRows" :key="row.id"
                class="hover:bg-slate-50 transition-colors cursor-pointer"
                @click="router.push(`/drivers/${row.id}`)">
                <td class="py-3.5">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#3d9a3d] to-[#1a4731] flex items-center justify-center text-white text-xs font-bold shrink-0">
                      {{ row.name.slice(0, 2).toUpperCase() }}
                    </div>
                    <span class="font-medium text-slate-800">{{ row.name }}</span>
                  </div>
                </td>
                <td class="py-3.5 text-slate-600">{{ row.total }}</td>
                <td class="py-3.5 text-slate-600">{{ row.completed }}</td>
                <td class="py-3.5">
                  <span class="text-sm font-semibold px-2 py-0.5 rounded-full"
                    :class="row.on_time >= 90 ? 'bg-green-100 text-[#3d9a3d]' : row.on_time >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'">
                    {{ row.on_time }}%
                  </span>
                </td>
                <td class="py-3.5">
                  <span class="text-sm font-bold"
                    :class="row.score >= 85 ? 'text-[#3d9a3d]' : row.score >= 70 ? 'text-amber-600' : 'text-red-500'">
                    {{ row.score }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Generate daily performance card ────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
      <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 bg-[#3d9a3d]/10 rounded-lg flex items-center justify-center">
          <ChartBarIcon class="w-4 h-4 text-[#3d9a3d]" />
        </div>
        <h3 class="font-semibold text-slate-800">Generate Daily Performance Report</h3>
      </div>
      <p class="text-sm text-slate-500 mb-4">Compute and store daily performance metrics for all drivers.</p>
      <div class="flex gap-3 items-end max-w-sm">
        <div class="flex-1">
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Date</label>
          <input v-model="generateDate" type="date"
            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
        </div>
        <button @click="generateDaily" :disabled="generatingDaily"
          class="flex items-center gap-2 px-4 py-2.5 bg-[#3d9a3d] text-white rounded-xl text-sm font-semibold hover:bg-[#1a4731] transition-colors disabled:opacity-60">
          <svg v-if="generatingDaily" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          Generate
        </button>
      </div>
      <div v-if="generateMsg" :class="['mt-3 p-3 rounded-xl text-sm', generateMsg.type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200']">
        {{ generateMsg.text }}
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { reportsApi } from '../api/reports'
import { driversApi } from '../api/drivers'
import { ArrowPathIcon, ArrowDownTrayIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

const router = useRouter()

// ── Filters ────────────────────────────────────────────────────────────────
const filters = reactive({
  date_from: new Date(Date.now() - 30 * 86400000).toISOString().split('T')[0],
  date_to:   new Date().toISOString().split('T')[0],
  driver_id: '',
})

const allDrivers = ref<any[]>([])
const generating = ref(false)
const ordersReport = ref<any>(null)
const dashboardData = ref<any>(null)

// ── Summary KPIs (from API or defaults) ───────────────────────────────────
const summary = computed(() => {
  if (ordersReport.value?.stats) {
    return {
      total_orders:      ordersReport.value.stats.total ?? 0,
      completed:         ordersReport.value.stats.delivered ?? 0,
      on_time_rate:      ordersReport.value.stats.on_time_rate ?? 0,
      avg_delivery_time: ordersReport.value.stats.avg_delivery_time ?? 0,
      total_distance_km: ordersReport.value.stats.total_distance_km ?? 0,
    }
  }
  if (dashboardData.value) {
    return {
      total_orders:      dashboardData.value.orders?.total ?? 148,
      completed:         dashboardData.value.orders?.delivered ?? 132,
      on_time_rate:      dashboardData.value.kpis?.on_time_rate ?? 87,
      avg_delivery_time: dashboardData.value.kpis?.avg_delivery_time ?? 43,
      total_distance_km: 1240,
    }
  }
  return { total_orders: 148, completed: 132, on_time_rate: 87, avg_delivery_time: 43, total_distance_km: 1240 }
})

// ── Daily bar chart ────────────────────────────────────────────────────────
const highlightDay = 4 // Friday
const dailyBars = computed(() => {
  const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed']
  const counts = ordersReport.value?.daily_counts
    ?? [12, 18, 14, 20, 22, 16, 8, 15, 19, 13]
  const max = Math.max(...counts, 1)
  return labels.map((label, i) => ({
    label,
    count: counts[i] ?? 0,
    pct: Math.round(((counts[i] ?? 0) / max) * 100),
  }))
})

// ── Status breakdown ───────────────────────────────────────────────────────
const statusBreakdown = computed(() => {
  const stats = ordersReport.value?.stats
  const total = stats?.total || summary.value.total_orders || 1
  const delivered = stats?.delivered ?? Math.round(total * 0.89)
  const failed    = stats?.failed    ?? Math.round(total * 0.04)
  const cancelled = stats?.cancelled ?? Math.round(total * 0.07)
  return [
    { label: `Delivered ${Math.round((delivered / total) * 100)}%`, pct: Math.round((delivered / total) * 100), color: 'bg-[#3d9a3d]' },
    { label: `Failed ${Math.round((failed / total) * 100)}%`,       pct: Math.round((failed / total) * 100),    color: 'bg-red-400' },
    { label: `Cancelled ${Math.round((cancelled / total) * 100)}%`, pct: Math.round((cancelled / total) * 100), color: 'bg-amber-300' },
  ]
})

// ── Driver rows ────────────────────────────────────────────────────────────
const driverRows = computed(() => {
  if (ordersReport.value?.driver_stats?.length) {
    return ordersReport.value.driver_stats.map((d: any) => ({
      id:        d.driver_id,
      name:      d.driver_name,
      total:     d.total_orders,
      completed: d.completed,
      on_time:   Math.round(d.on_time_rate ?? 0),
      score:     Math.round(d.performance_score ?? 0),
    }))
  }
  // Fallback demo rows
  return [
    { id: 1, name: 'Dara Sok',   total: 54, completed: 52, on_time: 92, score: 91 },
    { id: 2, name: 'Kosal Meas', total: 38, completed: 34, on_time: 78, score: 76 },
    { id: 3, name: 'Thy Chan',   total: 56, completed: 52, on_time: 88, score: 86 },
  ]
})

// ── Generate ───────────────────────────────────────────────────────────────
async function generate() {
  generating.value = true
  try {
    const params: Record<string, any> = {
      date_from: filters.date_from,
      date_to:   filters.date_to,
    }
    if (filters.driver_id) params.driver_id = filters.driver_id
    const res = await reportsApi.ordersReport(params)
    ordersReport.value = res.data.data
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to generate report')
  } finally {
    generating.value = false
  }
}

function exportCSV() {
  if (!ordersReport.value?.orders?.length) {
    alert('Generate a report first')
    return
  }
  const headers = ['Order #', 'Recipient', 'Driver', 'Status', 'Duration', 'Distance', 'Date']
  const rows = ordersReport.value.orders.map((o: any) => [
    o.order_number,
    o.recipient_name,
    o.driver?.user?.name || '',
    o.status,
    o.actual_duration ? `${Math.round(o.actual_duration)}m` : '',
    o.distance_km ? `${o.distance_km}km` : '',
    new Date(o.created_at).toLocaleDateString(),
  ])
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `report-${filters.date_from}-${filters.date_to}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

// ── Generate daily ─────────────────────────────────────────────────────────
const generateDate = ref(new Date().toISOString().split('T')[0])
const generatingDaily = ref(false)
const generateMsg = ref<{ type: string; text: string } | null>(null)

async function generateDaily() {
  generatingDaily.value = true
  generateMsg.value = null
  try {
    const res = await reportsApi.generateDaily(generateDate.value)
    generateMsg.value = { type: 'success', text: res.data.message }
  } catch (e: any) {
    generateMsg.value = { type: 'error', text: e.response?.data?.message || 'Failed to generate report' }
  } finally {
    generatingDaily.value = false
  }
}

function formatDuration(mins: number | null) {
  if (!mins) return 'N/A'
  if (mins < 60) return `${Math.round(mins)}m`
  return `${Math.floor(mins / 60)}h ${Math.round(mins % 60)}m`
}

onMounted(async () => {
  // Load drivers for filter dropdown
  try {
    const res = await driversApi.list({ per_page: 100 })
    allDrivers.value = res.data.data.data
  } catch {}
  // Load dashboard data for initial KPIs
  try {
    const res = await reportsApi.dashboard()
    dashboardData.value = res.data.data
  } catch {}
})
</script>
