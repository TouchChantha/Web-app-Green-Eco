<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('cancel')" />
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
          <div class="flex items-start gap-4">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0', iconBg]">
              <component :is="icon" :class="['w-5 h-5', iconColor]" />
            </div>
            <div class="flex-1">
              <h3 class="text-base font-semibold text-slate-800">{{ title }}</h3>
              <p class="text-sm text-slate-500 mt-1">{{ message }}</p>
              <div v-if="hasInput" class="mt-3">
                <input
                  v-model="inputValue"
                  :placeholder="inputPlaceholder"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#3d9a3d]"
                />
              </div>
            </div>
          </div>
          <div class="flex gap-3 mt-6 justify-end">
            <button
              @click="$emit('cancel')"
              class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="$emit('confirm', inputValue)"
              :class="['px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors', confirmClass]"
            >
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = withDefaults(defineProps<{
  show: boolean
  title: string
  message: string
  confirmText?: string
  variant?: 'danger' | 'warning' | 'info'
  icon?: any
  hasInput?: boolean
  inputPlaceholder?: string
}>(), {
  confirmText: 'Confirm',
  variant: 'danger',
  icon: ExclamationTriangleIcon,
})

defineEmits<{ confirm: [value: string]; cancel: [] }>()

const inputValue = ref('')

const iconBg = props.variant === 'danger' ? 'bg-red-100' : props.variant === 'warning' ? 'bg-amber-100' : 'bg-blue-100'
const iconColor = props.variant === 'danger' ? 'text-red-600' : props.variant === 'warning' ? 'text-amber-600' : 'text-blue-600'
const confirmClass = props.variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : props.variant === 'warning' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-[#3d9a3d] hover:bg-[#1a4731]'
</script>
