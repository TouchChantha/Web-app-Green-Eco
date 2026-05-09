<template>
  <div class="space-y-5">

    <!-- Back -->
    <RouterLink
      :to="auth.isDriver ? '/driver-portal' : '/orders'"
      class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#3d9a3d] transition-colors"
    >
      <ArrowLeftIcon class="w-4 h-4" />
      {{ auth.isDriver ? 'Back to My Deliveries' : 'Back to Orders' }}
    </RouterLink>

    <LoadingSpinner v-if="store.loading" text="Loading order..." :fullPage="true" />

    <template v-else-if="order">

      <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 items-start">

        <!-- ── Left: Order Detail Card ─────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
          <!-- Card header -->
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-slate-800">Order detail</h2>
            </div>
            <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-semibold">
              {{ auth.isAdmin ? 'Admin' : 'Driver' }}
            </span>
          </div>

          <div class="p-6 space-y-5">
            <!-- Order number + status -->
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xl font-bold text-slate-800 font-mono">{{ order.order_number }}</p>
                <p class="text-sm text-slate-500 mt-0.5">{{ order.recipient_name }} · {{ order.recipient_phone }}</p>
              </div>
              <StatusBadge :status="order.status" />
            </div>

            <!-- Route map placeholder -->
            <div class="relative bg-slate-50 rounded-xl h-44 overflow-hidden border border-slate-200">
              <div class="absolute inset-0 opacity-30"
                style="background-image: linear-gradient(#e2e8f0 1px, transparent 1px), linear-gradient(90deg, #e2e8f0 1px, transparent 1px); background-size: 28px 28px;">
              </div>
              <!-- Driver dot -->
              <div class="absolute top-1/3 left-1/4 w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-md"></div>
              <!-- Delivery point -->
              <div class="absolute bottom-1/3 right-1/4 flex flex-col items-center">
                <div class="w-5 h-5 rounded-full bg-red-500 border-2 border-white shadow-md"></div>
                <div class="w-0.5 h-2 bg-red-500"></div>
                <div class="w-1 h-1 rounded-full bg-red-500"></div>
              </div>
              <!-- Route line -->
              <svg class="absolute inset-0 w-full h-full" style="pointer-events:none">
                <line x1="25%" y1="33%" x2="75%" y2="67%" stroke="#3d9a3d" stroke-width="2" stroke-dasharray="6,4" opacity="0.6"/>
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <p class="text-xs text-slate-400 bg-white/80 px-3 py-1.5 rounded-lg">Route map — driver position + delivery point</p>
              </div>
            </div>

            <!-- Details grid -->
            <div class="space-y-3 text-sm">
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-500">Driver</span>
                <span class="font-medium text-slate-800">
                  {{ order.driver?.user?.name ?? 'Not assigned' }}
                </span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-500">ETA</span>
                <span v-if="order.route?.estimated_duration" class="font-medium text-blue-600">
                  {{ Math.round(order.route.estimated_duration) }} min remaining
                </span>
                <span v-else class="text-slate-400">—</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-500">Distance</span>
                <span class="font-medium text-slate-800">
                  {{ order.route?.total_distance_km ? `${order.route.total_distance_km} km` : order.distance_km ? `${order.distance_km} km` : '—' }}
                </span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-500">Priority</span>
                <PriorityBadge :priority="order.priority" />
              </div>
              <div v-if="order.pickup_address" class="flex justify-between items-start py-2 border-b border-slate-100 gap-4">
                <span class="text-slate-500 shrink-0">Pickup</span>
                <span class="font-medium text-slate-800 text-right text-xs">{{ order.pickup_address }}</span>
              </div>
              <div v-if="order.delivery_address" class="flex justify-between items-start py-2 gap-4">
                <span class="text-slate-500 shrink-0">Delivery</span>
                <span class="font-medium text-slate-800 text-right text-xs">{{ order.delivery_address }}</span>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="order.notes" class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
              <p class="text-xs text-amber-700"><span class="font-semibold">Note:</span> {{ order.notes }}</p>
            </div>

            <!-- Admin actions -->
            <div v-if="auth.isAdmin" class="flex flex-wrap gap-2 pt-2">
              <button v-if="order.status === 'pending'" @click="showAssignModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                <UserPlusIcon class="w-4 h-4" />
                Assign Driver
              </button>
              <button v-if="['assigned','in_transit'].includes(order.status)" @click="reOptimize" :disabled="optimizing"
                class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors disabled:opacity-60">
                <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': optimizing }" />
                Re-route
              </button>
              <button v-if="!['delivered','cancelled'].includes(order.status)" @click="showCancelModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-medium hover:bg-red-100 transition-colors">
                <XCircleIcon class="w-4 h-4" />
                Cancel
              </button>
            </div>

            <!-- Driver actions -->
            <div v-if="auth.isDriver" class="flex flex-wrap gap-2 pt-2">
              <button
                v-if="order.delivery_address && ['assigned', 'in_transit'].includes(order.status)"
                type="button"
                @click="openGoogleMapsDirections(order.delivery_address)"
                class="flex items-center gap-2 px-4 py-2.5 border border-[#3d9a3d] text-[#0d3320] rounded-xl text-sm font-semibold hover:bg-green-50 transition-colors w-full sm:w-auto justify-center"
              >
                <MapPinIcon class="w-4 h-4 text-[#3d9a3d]" />
                Navigate route
              </button>
              <button v-if="order.status === 'assigned'" @click="driverUpdateStatus('in_transit')" :disabled="actionLoading"
                class="flex items-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 transition-colors disabled:opacity-60 flex-1 justify-center">
                <TruckIcon class="w-4 h-4" />
                Start Delivery
              </button>
              <button v-if="order.status === 'in_transit'" @click="driverUpdateStatus('delivered')" :disabled="actionLoading"
                class="flex items-center gap-2 px-4 py-2.5 bg-[#3d9a3d] text-white rounded-xl text-sm font-semibold hover:bg-[#1a4731] transition-colors disabled:opacity-60 flex-1 justify-center">
                <CheckCircleIcon class="w-4 h-4" />
                Mark Delivered
              </button>
            </div>

            <!-- API endpoints -->
            <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
            </div>
          </div>
        </div>

        <!-- ── Right: Assign Driver Modal (inline on desktop) ─────────── -->
        <div v-if="auth.isAdmin && order.status === 'pending'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-slate-800">Assign driver modal</h2>
              <p class="text-xs text-slate-400 mt-0.5">Select an available driver for {{ order.order_number }}</p>
            </div>
            <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-semibold">Admin</span>
          </div>
          <div class="p-6">
            <!-- Search -->
            <div class="relative mb-4">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="driverSearch" placeholder="Search drivers..."
                class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <!-- Driver list -->
            <div class="space-y-2 max-h-80 overflow-y-auto">
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
                <div class="flex items-center gap-2 shrink-0">
                  <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full"
                      :class="d.status === 'available' ? 'bg-[#3d9a3d]' : d.status === 'on_delivery' ? 'bg-amber-400' : 'bg-slate-400'">
                    </span>
                    <span class="text-xs text-slate-600 capitalize">{{ d.status.replace('_', ' ') }}</span>
                  </div>
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
          </div>
        </div>

        <!-- Right: Status history (when not pending or driver view) -->
        <div v-else class="space-y-4">
          <!-- Route info -->
          <div v-if="order.route" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
              <MapIcon class="w-4 h-4 text-[#3d9a3d]" />
              Route Information
            </h3>
            <div class="grid grid-cols-3 gap-3">
              <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-200">
                <p class="text-xs text-slate-500">Distance</p>
                <p class="text-lg font-bold text-slate-800 mt-0.5">{{ order.route.total_distance_km }} km</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-200">
                <p class="text-xs text-slate-500">Est. Duration</p>
                <p class="text-lg font-bold text-slate-800 mt-0.5">{{ formatDuration(order.route.estimated_duration) }}</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-3 text-center border border-slate-200">
                <p class="text-xs text-slate-500">Optimized</p>
                <p class="text-lg font-bold mt-0.5" :class="order.route.optimized ? 'text-[#3d9a3d]' : 'text-slate-400'">
                  {{ order.route.optimized ? 'Yes ✓' : 'No' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Status history -->
          <div v-if="order.status_history?.length" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
              <ClockIcon class="w-4 h-4 text-[#3d9a3d]" />
              Order Timeline
            </h3>
            <div class="relative pl-4">
              <div class="absolute left-0 top-2 bottom-2 w-px bg-slate-200"></div>
              <div v-for="h in order.status_history" :key="h.id" class="relative flex items-start gap-3 pb-4 last:pb-0">
                <div class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-[#3d9a3d] border-2 border-white"></div>
                <div class="flex-1 ml-3">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span v-if="h.from_status" class="text-xs text-slate-400 capitalize">{{ h.from_status }}</span>
                    <span v-if="h.from_status" class="text-xs text-slate-300">→</span>
                    <StatusBadge :status="h.to_status" />
                  </div>
                  <p v-if="h.notes" class="text-xs text-slate-500 mt-0.5">{{ h.notes }}</p>
                  <p class="text-xs text-slate-400 mt-0.5">{{ formatDateTime(h.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </template>

    <!-- ── Assign Driver Modal (mobile / overlay) ──────────────────────── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showAssignModal = false" />
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full z-10 overflow-hidden">
            <div class="bg-gradient-to-r from-[#0d3320] to-[#1a4731] px-6 py-5">
              <h3 class="text-base font-semibold text-white">Assign Driver</h3>
              <p class="text-sm text-white/60 mt-0.5">Select an available driver for {{ order?.order_number }}</p>
            </div>
            <div class="p-6">
              <div class="relative mb-4">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input v-model="driverSearch" placeholder="Search drivers..."
                  class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
              </div>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div v-if="loadingDrivers" class="text-center py-6 text-slate-400 text-sm">Loading...</div>
                <div v-for="d in filteredDrivers" :key="d.id"
                  class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:border-[#3d9a3d] transition-all">
                  <div class="w-9 h-9 rounded-full bg-[#3d9a3d] flex items-center justify-center text-white font-bold text-sm shrink-0">
                    {{ d.user?.name?.slice(0, 2).toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800">{{ d.user?.name }}</p>
                    <p class="text-xs text-slate-400">{{ d.vehicle_type }} · {{ d.vehicle_plate }}</p>
                  </div>
                  <StatusBadge :status="d.status" />
                  <button v-if="d.status === 'available'" @click="assignDriver(d.id)" :disabled="assigning"
                    class="px-3 py-1.5 bg-[#0d3320] text-white text-xs font-semibold rounded-lg hover:bg-[#1a4731] transition-colors disabled:opacity-60">
                    Assign
                  </button>
                </div>
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

    <!-- Cancel Modal -->
    <ConfirmModal
      :show="showCancelModal"
      title="Cancel Order"
      message="Are you sure you want to cancel this order? This cannot be undone."
      confirm-text="Cancel Order"
      variant="danger"
      :has-input="true"
      input-placeholder="Reason for cancellation (optional)"
      @confirm="cancelOrder"
      @cancel="showCancelModal = false"
    />

    <!-- Fail Modal -->
    <ConfirmModal
      :show="showFailModal"
      title="Report Delivery Failure"
      message="Please provide a reason for the delivery failure."
      confirm-text="Mark Failed"
      variant="warning"
      :has-input="true"
      input-placeholder="Reason for failure"
      @confirm="(reason) => driverUpdateStatus('failed', reason)"
      @cancel="showFailModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useOrdersStore } from '../../stores/orders'
import { useDriversStore } from '../../stores/drivers'
import { routesApi } from '../../api/routes'
import { openGoogleMapsDirections } from '../../utils/maps'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import PriorityBadge from '../../components/ui/PriorityBadge.vue'
import LoadingSpinner from '../../components/ui/LoadingSpinner.vue'
import ConfirmModal from '../../components/ui/ConfirmModal.vue'
import {
  ArrowLeftIcon, TruckIcon, ClockIcon, MapIcon, MapPinIcon,
  CheckCircleIcon, XCircleIcon, ArrowPathIcon, UserPlusIcon, MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const store = useOrdersStore()
const driversStore = useDriversStore()
const route = useRoute()

const order = ref<any>(null)
const showAssignModal = ref(false)
const showCancelModal = ref(false)
const showFailModal = ref(false)
const driverSearch = ref('')
const loadingDrivers = ref(false)
const assigning = ref(false)
const optimizing = ref(false)
const actionLoading = ref(false)

const filteredDrivers = computed(() => {
  if (!driverSearch.value) return driversStore.drivers
  const q = driverSearch.value.toLowerCase()
  return driversStore.drivers.filter(d =>
    d.user?.name?.toLowerCase().includes(q) || d.vehicle_plate?.toLowerCase().includes(q)
  )
})

async function load() {
  await store.fetchOrder(Number(route.params.id))
  order.value = store.currentOrder
}

async function loadDrivers() {
  loadingDrivers.value = true
  await driversStore.fetchDrivers({ per_page: 50 })
  loadingDrivers.value = false
}

async function assignDriver(driverId: number) {
  assigning.value = true
  try {
    await store.assignDriver(Number(route.params.id), driverId)
    showAssignModal.value = false
    await load()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to assign driver')
  } finally {
    assigning.value = false
  }
}

async function cancelOrder(reason: string) {
  try {
    await store.cancelOrder(Number(route.params.id), reason)
    showCancelModal.value = false
    await load()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to cancel order')
  }
}

async function reOptimize() {
  optimizing.value = true
  try {
    await routesApi.reOptimize(Number(route.params.id))
    await load()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to re-optimize route')
  } finally {
    optimizing.value = false
  }
}

async function driverUpdateStatus(status: string, notes?: string) {
  actionLoading.value = true
  showFailModal.value = false
  try {
    await store.updateStatus(Number(route.params.id), { status, notes, delay_reason: notes })
    await load()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Failed to update status')
  } finally {
    actionLoading.value = false
  }
}

function formatDateTime(d: string) {
  return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function formatDuration(mins: number) {
  if (!mins) return 'N/A'
  if (mins < 60) return `${mins}m`
  return `${Math.floor(mins / 60)}h ${mins % 60}m`
}

onMounted(async () => {
  await load()
  if (auth.isAdmin) loadDrivers()
})

watch(showAssignModal, (v) => { if (v && auth.isAdmin) loadDrivers() })
</script>
