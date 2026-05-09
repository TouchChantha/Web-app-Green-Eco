<template>
  <div class="min-h-screen bg-[#f8fafc] flex">

    <!-- ── Left panel (branding) — hidden on mobile ───────────────────── -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#0d3320] via-[#1a4731] to-[#0d3320] flex-col justify-between p-12 relative overflow-hidden">
      <!-- Dot pattern -->
      <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 36px 36px;"></div>

      <!-- Logo -->
      <div class="relative flex items-center gap-3">
        <div class="w-10 h-10">
          <GecLogo />
        </div>
        <div>
          <p class="text-sm font-bold text-[#5cb85c] leading-tight">GREEN ECOCHAIN</p>
          <p class="text-xs text-white/40">Delivery Management</p>
        </div>
      </div>

      <!-- Center content -->
      <div class="relative">
        <div class="w-16 h-16 bg-[#5cb85c]/20 rounded-2xl flex items-center justify-center mb-6 border border-[#5cb85c]/30">
          <TruckIcon class="w-8 h-8 text-[#5cb85c]" />
        </div>
        <h1 class="text-4xl font-extrabold text-white leading-tight mb-4">
          Smarter<br />
          <span class="text-[#5cb85c]">Delivery</span><br />
          Management
        </h1>
        <p class="text-white/50 text-base leading-relaxed max-w-sm">
          Real-time tracking, intelligent route optimization, and driver performance analytics — all in one platform.
        </p>

        <!-- Feature pills -->
        <div class="flex flex-wrap gap-2 mt-8">
          <span v-for="f in features" :key="f"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 text-white/70 text-xs rounded-full border border-white/10">
            <span class="w-1.5 h-1.5 rounded-full bg-[#5cb85c]"></span>
            {{ f }}
          </span>
        </div>
      </div>

      <!-- Bottom -->
      <p class="relative text-white/20 text-xs">© 2026 Green Ecochain Co., Ltd.</p>
    </div>

    <!-- ── Right panel (form) ──────────────────────────────────────────── -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
      <div class="w-full max-w-md">

        <!-- Mobile logo -->
        <div class="flex items-center gap-3 mb-8 lg:hidden">
          <div class="w-9 h-9">
            <GecLogo />
          </div>
          <div>
            <p class="text-sm font-bold text-[#3d9a3d]">GREEN ECOCHAIN</p>
            <p class="text-xs text-slate-400">Delivery Management</p>
          </div>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 bg-[#3d9a3d]/10 rounded-lg flex items-center justify-center">
              <TruckIcon class="w-4 h-4 text-[#3d9a3d]" />
            </div>
            <span class="text-xs font-semibold text-[#3d9a3d] uppercase tracking-widest">DeliveryMS</span>
          </div>
          <h2 class="text-2xl font-bold text-slate-800">Sign in to your account</h2>
          <p class="text-slate-500 text-sm mt-1">Redirects by role after login</p>
        </div>

        <!-- Form card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
          <form @submit.prevent="handleLogin" class="space-y-5">

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
              <div class="relative">
                <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="admin@delivery.com"
                  class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all"
                  :class="{ 'border-red-400 focus:ring-red-400': errors.email }"
                />
              </div>
              <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
              <div class="relative">
                <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  placeholder="••••••••"
                  class="w-full pl-10 pr-10 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] focus:border-transparent transition-all"
                  :class="{ 'border-red-400 focus:ring-red-400': errors.password }"
                />
                <button type="button" @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                  <EyeIcon v-if="!showPassword" class="w-4 h-4" />
                  <EyeSlashIcon v-else class="w-4 h-4" />
                </button>
              </div>
              <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
            </div>

            <!-- Error alert -->
            <div v-if="loginError" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2">
              <ExclamationCircleIcon class="w-4 h-4 text-red-500 shrink-0" />
              <p class="text-sm text-red-600">{{ loginError }}</p>
            </div>

            <!-- Submit -->
            <button
              type="submit"
              :disabled="auth.loading"
              class="w-full bg-[#0d3320] hover:bg-[#1a4731] disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 mt-2 shadow-sm"
            >
              <svg v-if="auth.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ auth.loading ? 'Signing in...' : 'Sign in' }}
            </button>
          </form>
        </div>



      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon,
  ExclamationCircleIcon, TruckIcon,
} from '@heroicons/vue/24/outline'
import GecLogo from '../components/GecLogo.vue'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const loginError = ref('')
const showPassword = ref(false)

const features = ['Real-time GPS Tracking', 'Route Optimization', 'Driver Management', 'Performance Analytics']

async function handleLogin() {
  errors.email = ''
  errors.password = ''
  loginError.value = ''

  try {
    const data = await auth.login(form.email, form.password)
    const role = data.user.role
    router.push(role === 'admin' ? '/dashboard' : '/driver-portal')
  } catch (e: any) {
    loginError.value = e.response?.data?.message || 'Login failed. Please try again.'
  }
}
</script>
