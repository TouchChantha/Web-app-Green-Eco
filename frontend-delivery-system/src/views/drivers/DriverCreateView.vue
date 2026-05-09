<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <RouterLink to="/drivers" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#3d9a3d] transition-colors">
      <ArrowLeftIcon class="w-4 h-4" />
      Back to Drivers
    </RouterLink>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="bg-gradient-to-r from-[#0d3320] to-[#1a4731] px-6 py-5">
        <h2 class="text-lg font-semibold text-white">Add New Driver</h2>
        <p class="text-white/60 text-sm mt-1">Create a driver account and vehicle profile</p>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Account Info -->
        <div>
          <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <UserIcon class="w-4 h-4 text-[#3d9a3d]" />
            Account Information
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Full Name *</label>
              <input v-model="form.name" required placeholder="Dara Sok"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Email *</label>
              <input v-model="form.email" type="email" required placeholder="dara@example.com"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
              <input v-model="form.phone" placeholder="+855 12 345 678"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Password *</label>
              <input v-model="form.password" type="password" required placeholder="Min 8 characters"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-slate-600 mb-1">Confirm Password *</label>
              <input v-model="form.password_confirmation" type="password" required placeholder="Repeat password"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
          </div>
        </div>

        <!-- Vehicle Info -->
        <div>
          <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <TruckIcon class="w-4 h-4 text-[#3d9a3d]" />
            Vehicle Information
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">License Number *</label>
              <input v-model="form.license_number" required placeholder="KH-12345"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-600 mb-1">Vehicle Plate *</label>
              <input v-model="form.vehicle_plate" required placeholder="PP-1234A"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-slate-600 mb-1">Vehicle Type *</label>
              <select v-model="form.vehicle_type" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d] bg-white">
                <option value="">Select vehicle type</option>
                <option value="motorcycle">Motorcycle</option>
                <option value="car">Car</option>
                <option value="van">Van</option>
                <option value="truck">Truck</option>
              </select>
            </div>
          </div>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600">{{ error }}</div>

        <div class="flex gap-3 pt-2">
          <RouterLink to="/drivers" class="flex-1 text-center px-4 py-2.5 border border-slate-300 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
            Cancel
          </RouterLink>
          <button type="submit" :disabled="submitting"
            class="flex-1 bg-[#3d9a3d] hover:bg-[#1a4731] disabled:opacity-60 text-white font-semibold py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2">
            <svg v-if="submitting" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ submitting ? 'Creating...' : 'Create Driver' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useDriversStore } from '../../stores/drivers'
import { ArrowLeftIcon, UserIcon, TruckIcon } from '@heroicons/vue/24/outline'

const store = useDriversStore()
const router = useRouter()
const submitting = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  license_number: '',
  vehicle_plate: '',
  vehicle_type: '',
})

async function handleSubmit() {
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }
  submitting.value = true
  error.value = ''
  try {
    const res = await store.createDriver(form)
    router.push(`/drivers/${res.data.id}`)
  } catch (e: any) {
    const errs = e.response?.data?.errors
    if (errs) {
      error.value = Object.values(errs).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Failed to create driver'
    }
  } finally {
    submitting.value = false
  }
}
</script>
