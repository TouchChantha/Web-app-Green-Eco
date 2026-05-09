<template>
  <div class="flex h-screen bg-slate-50 overflow-hidden">

    <!-- ── Sidebar ─────────────────────────────────────────────────────── -->
    <aside :class="[
      'flex flex-col bg-[#0d3320] text-white transition-all duration-300 ease-in-out shrink-0 z-30',
      sidebarOpen ? 'w-64' : 'w-16',
    ]">

      <!-- Logo -->
      <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10 min-h-[72px]">
        <div class="shrink-0 w-8 h-8">
          <GecLogo />
        </div>
        <Transition name="fade">
          <div v-if="sidebarOpen" class="overflow-hidden">
            <p class="text-xs font-bold text-[#5cb85c] leading-tight whitespace-nowrap">GREEN ECOCHAIN</p>
            <p class="text-[10px] text-white/50 whitespace-nowrap">Delivery Management</p>
          </div>
        </Transition>
      </div>

      <!-- Role badge -->
      <Transition name="fade">
        <div v-if="sidebarOpen" class="mx-3 mt-3 mb-1">
          <span :class="[
            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold w-full justify-center',
            auth.isAdmin ? 'bg-[#5cb85c]/20 text-[#5cb85c]' : 'bg-blue-500/20 text-blue-300'
          ]">
            <ShieldCheckIcon v-if="auth.isAdmin" class="w-3.5 h-3.5" />
            <TruckIcon v-else class="w-3.5 h-3.5" />
            {{ auth.isAdmin ? t.administrator : t.deliveryDriver }}
          </span>
        </div>
      </Transition>

      <!-- Navigation -->
      <nav class="flex-1 py-3 overflow-y-auto overflow-x-hidden space-y-0.5 px-2">

        <!-- Section label -->
        <Transition name="fade">
          <p v-if="sidebarOpen" class="px-2 pt-2 pb-1 text-[10px] font-semibold text-white/30 uppercase tracking-widest">
            {{ auth.isAdmin ? t.administrator : t.myDeliveries }}
          </p>
        </Transition>

        <template v-for="item in visibleNavItems" :key="item.name">
          <RouterLink
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 group relative"
            :class="isActive(item.to)
              ? 'bg-[#5cb85c] text-white shadow-sm'
              : 'text-white/65 hover:bg-white/10 hover:text-white'"
          >
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <Transition name="fade">
              <span v-if="sidebarOpen" class="text-sm font-medium whitespace-nowrap">{{ item.name }}</span>
            </Transition>
            <!-- Collapsed tooltip -->
            <div v-if="!sidebarOpen"
              class="absolute left-full ml-3 px-2.5 py-1.5 bg-slate-900 text-white text-xs rounded-lg whitespace-nowrap
                     opacity-0 group-hover:opacity-100 pointer-events-none z-50 transition-opacity shadow-lg">
              {{ item.name }}
            </div>
          </RouterLink>
        </template>
      </nav>

      <!-- User profile + logout -->
      <div class="border-t border-white/10 p-3 space-y-1">
        <!-- User info -->
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl">
          <div class="w-8 h-8 rounded-full bg-[#5cb85c] flex items-center justify-center shrink-0 text-sm font-bold shadow">
            {{ userInitial }}
          </div>
          <Transition name="fade">
            <div v-if="sidebarOpen" class="flex-1 min-w-0">
              <p class="text-sm font-semibold truncate leading-tight">{{ auth.user?.name }}</p>
              <p class="text-[11px] text-white/40 truncate">{{ auth.user?.email }}</p>
            </div>
          </Transition>
        </div>

        <!-- Logout -->
        <button
          @click="handleLogout"
          class="flex items-center gap-3 w-full px-3 py-2 rounded-xl text-white/50 hover:bg-red-500/15 hover:text-red-300 transition-all group relative"
        >
          <ArrowRightOnRectangleIcon class="w-5 h-5 shrink-0" />
          <Transition name="fade">
            <span v-if="sidebarOpen" class="text-sm">{{ t.signOut }}</span>
          </Transition>
          <div v-if="!sidebarOpen"
            class="absolute left-full ml-3 px-2.5 py-1.5 bg-slate-900 text-white text-xs rounded-lg whitespace-nowrap
                   opacity-0 group-hover:opacity-100 pointer-events-none z-50 transition-opacity shadow-lg">
            {{ t.signOut }}
          </div>
        </button>
      </div>
    </aside>

    <!-- ── Main area ───────────────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

      <!-- Top bar -->
      <header class="bg-white border-b border-slate-200 px-5 py-3.5 flex items-center gap-4 shrink-0 shadow-sm">
        <!-- Sidebar toggle -->
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
        >
          <Bars3Icon class="w-5 h-5" />
        </button>

        <!-- Breadcrumb / title -->
        <div class="flex-1 min-w-0">
          <h1 class="text-base font-semibold text-slate-800 truncate">{{ pageTitle }}</h1>
        </div>

        <!-- Right side: date + role chip + language switcher -->
        <div class="flex items-center gap-3">
          <span class="hidden sm:block text-xs text-slate-400">{{ currentDate }}</span>
          <!-- Language switcher -->
          <button
            @click="toggleLang"
            class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors"
            :title="lang === 'en' ? 'Switch to Khmer' : 'Switch to English'"
          >
            <span class="text-base leading-none">{{ lang === 'en' ? '🇰🇭' : '🇬🇧' }}</span>
            {{ lang === 'en' ? 'ខ្មែរ' : 'EN' }}
          </button>
          <span :class="[
            'hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold',
            auth.isAdmin ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'
          ]">
            <span class="w-1.5 h-1.5 rounded-full" :class="auth.isAdmin ? 'bg-green-500' : 'bg-blue-500'"></span>
            {{ auth.isAdmin ? t.admin : t.deliveryDriver }}
          </span>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  Squares2X2Icon,
  ClipboardDocumentListIcon,
  TruckIcon,
  ChartBarIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  MapIcon,
  ShieldCheckIcon,
  PlusCircleIcon,
  ArchiveBoxIcon,
} from '@heroicons/vue/24/outline'
import GecLogo from '../components/GecLogo.vue'
import { useDriverLocationTracking } from '../composables/useDriverLocationTracking'
import { useI18n } from '../composables/useI18n'

const auth = useAuthStore()
const { t, lang, setLang } = useI18n()

function toggleLang() {
  setLang(lang.value === 'en' ? 'km' : 'en')
}

useDriverLocationTracking()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(true)

// ── All nav items with role restrictions ──────────────────────────────────
const allNavItems = computed(() => [
  // Admin only
  { name: t.value.dashboard,    to: '/dashboard',      icon: Squares2X2Icon,            roles: ['admin'] },
  { name: t.value.orders,       to: '/orders',         icon: ClipboardDocumentListIcon, roles: ['admin'] },
  { name: t.value.newOrder,     to: '/orders/create',  icon: PlusCircleIcon,            roles: ['admin'] },
  { name: t.value.drivers,      to: '/drivers',        icon: TruckIcon,                 roles: ['admin'] },
  { name: t.value.liveMap,      to: '/live-tracking',  icon: MapIcon,                   roles: ['admin'] },
  { name: t.value.reports,      to: '/reports',        icon: ChartBarIcon,              roles: ['admin'] },
  // Driver only
  { name: t.value.myDeliveries, to: '/driver-portal',  icon: ArchiveBoxIcon,            roles: ['driver'] },
  { name: t.value.orderHistory, to: '/orders',         icon: ClipboardDocumentListIcon, roles: ['driver'] },
])

// Only show items the current user's role can access
const visibleNavItems = computed(() =>
  allNavItems.value.filter(item => item.roles.includes(auth.user?.role ?? ''))
)

function isActive(to: string) {
  if (to === '/orders' && route.path.startsWith('/orders')) return true
  if (to === '/drivers' && route.path.startsWith('/drivers')) return true
  return route.path === to
}

const pageTitle = computed(() => {
  const map: Record<string, string> = {
    '/dashboard':     t.value.dashboard,
    '/orders':        auth.isAdmin ? t.value.orders : t.value.myOrders,
    '/orders/create': t.value.createOrder,
    '/drivers':       t.value.driverMgmt,
    '/drivers/create':t.value.addNewDriver,
    '/reports':       t.value.analyticsTitle,
    '/driver-portal': t.value.myDeliveries,
    '/live-tracking': t.value.liveTrackingTitle,
  }
  if (route.path.match(/^\/orders\/\d+$/))  return t.value.orderNum
  if (route.path.match(/^\/drivers\/\d+$/)) return t.value.driverProfile
  return map[route.path] || 'Green Ecochain'
})

const userInitial = computed(() => auth.user?.name?.charAt(0).toUpperCase() || 'U')

const currentDate = computed(() =>
  new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
)

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>
