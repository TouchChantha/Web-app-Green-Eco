<template>
  <div class="space-y-5">

    <!-- ── Header ──────────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-800">Drivers list</h2>
        <p class="text-sm text-slate-500 mt-0.5">Manage your delivery driver fleet</p>
      </div>
      <RouterLink to="/drivers/create"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0d3320] text-white rounded-xl text-sm font-semibold hover:bg-[#1a4731] transition-colors shadow-sm">
        <PlusIcon class="w-4 h-4" />
        + Add driver
      </RouterLink>
    </div>

    <!-- ── Filters ─────────────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
      <div class="flex flex-wrap gap-3 items-center">
        <div class="flex-1 min-w-44">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input v-model="searchQuery" placeholder="Search drivers..."
              class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white" />
          </div>
        </div>
        <select v-model="filterStatus" @change="load()"
          class="border border-slate-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white cursor-pointer">
          <option value="">Status ▾</option>
          <option value="available">Available</option>
          <option value="on_delivery">On delivery</option>
          <option value="offline">Offline</option>
        </select>
      </div>
    </div>

    <LoadingSpinner v-if="store.loading" text="Loading drivers..." :fullPage="true" />

    <template v-else>
      <!-- ── Drivers List ─────────────────────────────────────────────── -->
      <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="divide-y divide-slate-100">
          <div v-if="filteredDrivers.length === 0" class="py-16 text-center">
            <TruckIcon class="w-10 h-10 mx-auto text-slate-300 mb-3" />
            <p class="text-slate-500 font-medium">No drivers found</p>
            <RouterLink to="/drivers/create" class="mt-2 inline-flex items-center gap-1 text-sm text-[#3d9a3d] hover:underline">
              <PlusIcon class="w-4 h-4" /> Add your first driver
            </RouterLink>
          </div>

          <div v-for="driver in filteredDrivers" :key="driver.id"
            class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors cursor-pointer group"
            @click="router.push(`/drivers/${driver.id}`)">

            <!-- Avatar -->
            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#3d9a3d] to-[#1a4731] flex items-center justify-center text-white font-bold text-base shrink-0">
              {{ driver.user?.name?.slice(0, 2).toUpperCase() }}
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-slate-800 group-hover:text-[#3d9a3d] transition-colors">{{ driver.user?.name }}</p>
              <p class="text-xs text-slate-400 mt-0.5">{{ driver.vehicle_plate }} · {{ driver.vehicle_type }}</p>
            </div>

            <!-- Status dot + label -->
            <div class="flex items-center gap-1.5 shrink-0">
              <span class="w-2 h-2 rounded-full"
                :class="driver.status === 'available' ? 'bg-[#3d9a3d]' : driver.status === 'on_delivery' ? 'bg-amber-400' : 'bg-slate-400'">
              </span>
              <span class="text-sm text-slate-600 capitalize hidden sm:block">{{ driver.status.replace('_', ' ') }}</span>
            </div>

            <!-- Orders count -->
            <div class="text-center shrink-0 hidden md:block">
              <p class="text-sm font-bold text-slate-800">{{ driver.orders_count || 0 }}</p>
              <p class="text-xs text-slate-400">orders</p>
            </div>

            <!-- On-time rate -->
            <div class="text-center shrink-0 hidden lg:block">
              <p class="text-sm font-bold" :class="getOnTimeColor(driver.on_time_rate)">
                {{ driver.on_time_rate != null ? `${Math.round(driver.on_time_rate)}%` : '—' }}
              </p>
              <p class="text-xs text-slate-400">on-time</p>
            </div>

            <!-- Arrow -->
            <ChevronRightIcon class="w-4 h-4 text-slate-300 group-hover:text-[#3d9a3d] transition-colors shrink-0" />
          </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100">
          <p class="text-xs text-slate-400">% = on-time rate · Click row → driver detail</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="store.pagination && store.pagination.last_page > 1" class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ store.pagination.total }} drivers total</p>
        <div class="flex gap-2">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
            class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg disabled:opacity-40 hover:bg-white transition-colors">← Prev</button>
          <span class="px-3 py-1.5 text-xs text-slate-600 bg-white border border-slate-200 rounded-lg">
            {{ currentPage }} / {{ store.pagination.last_page }}
          </span>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= store.pagination.last_page"
            class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg disabled:opacity-40 hover:bg-white transition-colors">Next →</button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useDriversStore } from '../../stores/drivers'
import LoadingSpinner from '../../components/ui/LoadingSpinner.vue'
import { PlusIcon, TruckIcon, MagnifyingGlassIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const store = useDriversStore()
const router = useRouter()
const filterStatus = ref('')
const searchQuery = ref('')
const currentPage = ref(1)

const filteredDrivers = computed(() => {
  if (!searchQuery.value) return store.drivers
  const q = searchQuery.value.toLowerCase()
  return store.drivers.filter(d =>
    d.user?.name?.toLowerCase().includes(q) ||
    d.vehicle_plate?.toLowerCase().includes(q) ||
    d.vehicle_type?.toLowerCase().includes(q)
  )
})

function getOnTimeColor(rate: number | null) {
  if (rate == null) return 'text-slate-400'
  if (rate >= 90) return 'text-[#3d9a3d]'
  if (rate >= 75) return 'text-amber-500'
  return 'text-red-500'
}

function load() {
  const params: Record<string, any> = { page: currentPage.value, per_page: 20 }
  if (filterStatus.value) params.status = filterStatus.value
  store.fetchDrivers(params)
}

function changePage(page: number) {
  currentPage.value = page
  load()
}

onMounted(load)
</script>
