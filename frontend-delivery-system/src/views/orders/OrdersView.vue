<template>
  <div class="space-y-5">

    <!-- ── Header ──────────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800">{{ auth.isAdmin ? 'Orders list' : 'My Orders' }}</h2>
        <p class="text-sm text-slate-500 mt-0.5">
          {{ auth.isAdmin ? 'Manage and track all delivery orders' : 'View your assigned delivery orders' }}
        </p>
      </div>
      <RouterLink
        v-if="auth.isAdmin"
        to="/orders/create"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0d3320] text-white rounded-xl text-sm font-semibold hover:bg-[#1a4731] transition-colors shadow-sm"
      >
        <PlusIcon class="w-4 h-4" />
        + New order
      </RouterLink>
    </div>

    <!-- ── Filters bar ─────────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
      <div class="flex flex-wrap gap-3 items-center">
        <!-- Search -->
        <div v-if="auth.isAdmin" class="flex-1 min-w-44">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              v-model="filters.search"
              placeholder="Search orders..."
              class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white"
            />
          </div>
        </div>

        <!-- Status -->
        <div class="relative">
          <select v-model="filters.status"
            class="appearance-none border border-slate-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white cursor-pointer">
            <option value="">Status ▾</option>
            <option v-if="auth.isAdmin" value="pending">Pending</option>
            <option value="assigned">Assigned</option>
            <option value="in_transit">In Transit</option>
            <option value="delivered">Delivered</option>
            <option value="failed">Failed</option>
            <option v-if="auth.isAdmin" value="cancelled">Cancelled</option>
          </select>
        </div>

        <!-- Priority (admin only) -->
        <div v-if="auth.isAdmin" class="relative">
          <select v-model="filters.priority"
            class="appearance-none border border-slate-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white cursor-pointer">
            <option value="">Priority ▾</option>
            <option value="low">Low</option>
            <option value="normal">Normal</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <!-- Date range (admin only) -->
        <template v-if="auth.isAdmin">
          <div class="relative">
            <button @click="showDatePicker = !showDatePicker"
              class="flex items-center gap-2 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors bg-white">
              <CalendarIcon class="w-4 h-4 text-slate-400" />
              {{ filters.date_from || filters.date_to ? `${filters.date_from || '...'} → ${filters.date_to || '...'}` : 'Date range' }}
            </button>
            <div v-if="showDatePicker"
              class="absolute top-full left-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg p-4 z-20 flex gap-3 min-w-64">
              <div>
                <label class="text-xs text-slate-500 mb-1 block">From</label>
                <input v-model="filters.date_from" type="date"
                  class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
              </div>
              <div>
                <label class="text-xs text-slate-500 mb-1 block">To</label>
                <input v-model="filters.date_to" type="date"
                  class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
              </div>
              <button @click="showDatePicker = false"
                class="self-end px-3 py-1.5 bg-[#3d9a3d] text-white rounded-lg text-xs font-medium">Done</button>
            </div>
          </div>
        </template>

        <button @click="applyFilters"
          class="px-4 py-2 bg-[#3d9a3d] text-white rounded-lg text-sm font-medium hover:bg-[#1a4731] transition-colors">
          Filter
        </button>
        <button @click="resetFilters"
          class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
          Reset
        </button>

        <!-- Active filter chips -->
        <div class="flex flex-wrap gap-1.5 ml-auto">
          <span v-if="filters.status"
            class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs rounded-full border border-blue-200">
            {{ filters.status }}
            <button @click="filters.status = ''; applyFilters()" class="hover:text-blue-900">×</button>
          </span>
          <span v-if="filters.priority"
            class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs rounded-full border border-amber-200">
            {{ filters.priority }}
            <button @click="filters.priority = ''; applyFilters()" class="hover:text-amber-900">×</button>
          </span>
        </div>
      </div>
    </div>

    <!-- ── Table ────────────────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
      <LoadingSpinner v-if="store.loading" text="Loading orders..." :fullPage="true" />

      <template v-else>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Order #</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Recipient</th>
                <th v-if="auth.isAdmin" class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Driver</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden sm:table-cell">ETA</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="store.orders.length === 0">
                <td :colspan="auth.isAdmin ? 6 : 5" class="text-center py-16 text-slate-400">
                  <ClipboardDocumentListIcon class="w-10 h-10 mx-auto mb-2 opacity-30" />
                  <p class="font-medium">No orders found</p>
                  <p class="text-xs mt-1">{{ auth.isAdmin ? 'Create your first order to get started.' : 'No orders assigned yet.' }}</p>
                </td>
              </tr>
              <tr
                v-for="order in store.orders"
                :key="order.id"
                class="hover:bg-slate-50 transition-colors cursor-pointer group"
                @click="router.push(`/orders/${order.id}`)"
              >
                <td class="px-5 py-3.5">
                  <span class="font-mono text-xs font-bold text-[#3d9a3d]">{{ order.order_number }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <p class="font-medium text-slate-800">{{ order.recipient_name }}</p>
                  <p class="text-xs text-slate-400 hidden sm:block truncate max-w-36">{{ order.delivery_address }}</p>
                </td>
                <td v-if="auth.isAdmin" class="px-5 py-3.5 hidden lg:table-cell">
                  <span v-if="order.driver" class="text-slate-700">{{ order.driver.user?.name }}</span>
                  <span v-else class="text-slate-400 text-xs italic">—</span>
                </td>
                <td class="px-5 py-3.5">
                  <StatusBadge :status="order.status" />
                </td>
                <td class="px-5 py-3.5 hidden sm:table-cell">
                  <span v-if="order.route?.estimated_duration" class="text-sm text-slate-600">
                    {{ Math.round(order.route.estimated_duration) }} min
                  </span>
                  <span v-else class="text-slate-400 text-xs">—</span>
                </td>
                <td class="px-5 py-3.5 text-right" @click.stop>
                  <div class="flex items-center justify-end gap-2">
                    <!-- Driver quick actions -->
                    <template v-if="auth.isDriver">
                      <button v-if="order.status === 'assigned'" @click.stop="quickStart(order.id)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <TruckIcon class="w-3.5 h-3.5" />
                        Start
                      </button>
                      <button v-if="order.status === 'in_transit'" @click.stop="quickDeliver(order.id)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <CheckCircleIcon class="w-3.5 h-3.5" />
                        Delivered
                      </button>
                    </template>
                    <!-- Admin: assign if pending -->
                    <button v-if="auth.isAdmin && order.status === 'pending'"
                      @click.stop="openAssignModal(order)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                      Assign
                    </button>
                    <!-- View -->
                    <RouterLink :to="`/orders/${order.id}`"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-[#3d9a3d] bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                      <EyeIcon class="w-3.5 h-3.5" />
                      View
                    </RouterLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer: pagination + note -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-5 py-3 border-t border-slate-100 bg-slate-50 gap-3">
          <p class="text-xs text-slate-400">
            Click row → order detail. Pagination: 15 per page
            <span v-if="store.pagination"> · {{ store.pagination.total }} total</span>
          </p>
          <div v-if="store.pagination && store.pagination.last_page > 1" class="flex gap-2">
            <button @click="changePage(store.pagination.current_page - 1)"
              :disabled="store.pagination.current_page <= 1"
              class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg disabled:opacity-40 hover:bg-white transition-colors">
              ← Prev
            </button>
            <span class="px-3 py-1.5 text-xs text-slate-600 bg-white border border-slate-200 rounded-lg">
              {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
            </span>
            <button @click="changePage(store.pagination.current_page + 1)"
              :disabled="store.pagination.current_page >= store.pagination.last_page"
              class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg disabled:opacity-40 hover:bg-white transition-colors">
              Next →
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- ── Assign Driver Modal ─────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showAssignModal = false" />
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full z-10 overflow-hidden">
            <!-- Modal header -->
            <div class="bg-gradient-to-r from-[#0d3320] to-[#1a4731] px-6 py-5">
              <h3 class="text-base font-semibold text-white">Assign driver modal</h3>
              <p class="text-sm text-white/60 mt-0.5">Select an available driver for {{ assigningOrder?.order_number }}</p>
            </div>
            <div class="p-6">
              <!-- Search drivers -->
              <div class="relative mb-4">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input v-model="driverSearch" placeholder="Search drivers..."
                  class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
              </div>
              <!-- Driver list -->
              <div class="space-y-2 max-h-72 overflow-y-auto">
                <div v-if="loadingDrivers" class="text-center py-6 text-slate-400 text-sm">Loading drivers...</div>
                <div v-for="d in filteredDrivers" :key="d.id"
                  class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:border-[#3d9a3d] transition-all">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#3d9a3d] to-[#1a4731] flex items-center justify-center text-white font-bold text-sm shrink-0">
                    {{ d.user?.name?.slice(0, 2).toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800">{{ d.user?.name }}</p>
                    <p class="text-xs text-slate-400">{{ d.vehicle_type }} · {{ d.vehicle_plate }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <StatusBadge :status="d.status" />
                    <button v-if="d.status === 'available'"
                      @click="assignDriver(d.id)"
                      :disabled="assigning"
                      class="px-3 py-1.5 bg-[#0d3320] text-white text-xs font-semibold rounded-lg hover:bg-[#1a4731] transition-colors disabled:opacity-60">
                      Assign
                    </button>
                    <button v-else disabled
                      class="px-3 py-1.5 bg-slate-100 text-slate-400 text-xs font-semibold rounded-lg cursor-not-allowed">
                      Assign
                    </button>
                  </div>
                </div>
                <p v-if="!loadingDrivers && filteredDrivers.length === 0"
                  class="text-center py-6 text-slate-400 text-sm">No drivers found</p>
              </div>
              <button @click="showAssignModal = false"
                class="mt-4 w-full px-4 py-2.5 border border-slate-300 text-slate-600 rounded-xl text-sm hover:bg-slate-50 transition-colors">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useOrdersStore } from '../../stores/orders'
import { useDriversStore } from '../../stores/drivers'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import LoadingSpinner from '../../components/ui/LoadingSpinner.vue'
import {
  PlusIcon, MagnifyingGlassIcon, EyeIcon, CalendarIcon,
  ClipboardDocumentListIcon, TruckIcon, CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const store = useOrdersStore()
const driversStore = useDriversStore()
const router = useRouter()

const filters = reactive({ search: '', status: '', priority: '', date_from: '', date_to: '', page: 1 })
const showDatePicker = ref(false)
const showAssignModal = ref(false)
const assigningOrder = ref<any>(null)
const driverSearch = ref('')
const loadingDrivers = ref(false)
const assigning = ref(false)

const filteredDrivers = computed(() => {
  if (!driverSearch.value) return driversStore.drivers
  const q = driverSearch.value.toLowerCase()
  return driversStore.drivers.filter(d =>
    d.user?.name?.toLowerCase().includes(q) || d.vehicle_plate?.toLowerCase().includes(q)
  )
})

function buildParams() {
  const p: Record<string, any> = { page: filters.page, per_page: 15 }
  if (filters.status)    p.status    = filters.status
  if (filters.priority)  p.priority  = filters.priority
  if (filters.date_from) p.date_from = filters.date_from
  if (filters.date_to)   p.date_to   = filters.date_to
  return p
}

function applyFilters() { filters.page = 1; store.fetchOrders(buildParams()) }

function resetFilters() {
  Object.assign(filters, { search: '', status: '', priority: '', date_from: '', date_to: '', page: 1 })
  store.fetchOrders({ page: 1, per_page: 15 })
}

function changePage(page: number) { filters.page = page; store.fetchOrders(buildParams()) }

async function openAssignModal(order: any) {
  assigningOrder.value = order
  showAssignModal.value = true
  loadingDrivers.value = true
  await driversStore.fetchDrivers({ per_page: 50 })
  loadingDrivers.value = false
}

async function assignDriver(driverId: number) {
  if (!assigningOrder.value) return
  assigning.value = true
  try {
    await store.assignDriver(assigningOrder.value.id, driverId)
    showAssignModal.value = false
    store.fetchOrders(buildParams())
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to assign driver')
  } finally {
    assigning.value = false
  }
}

async function quickStart(orderId: number) {
  try {
    await store.updateStatus(orderId, { status: 'in_transit' })
    store.fetchOrders(buildParams())
  } catch (e: any) { alert(e.response?.data?.message || 'Failed') }
}

async function quickDeliver(orderId: number) {
  try {
    await store.updateStatus(orderId, { status: 'delivered' })
    store.fetchOrders(buildParams())
  } catch (e: any) { alert(e.response?.data?.message || 'Failed') }
}

onMounted(() => store.fetchOrders({ page: 1, per_page: 15 }))
</script>
