<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="['flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg max-w-sm pointer-events-auto', bgClass(toast.type)]"
        >
          <component :is="iconFor(toast.type)" class="w-5 h-5 flex-shrink-0 mt-0.5" :class="iconClass(toast.type)" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium" :class="textClass(toast.type)">{{ toast.message }}</p>
          </div>
          <button @click="remove(toast.id)" class="text-slate-400 hover:text-slate-600 flex-shrink-0">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { CheckCircleIcon, ExclamationCircleIcon, InformationCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

interface Toast { id: number; message: string; type: 'success' | 'error' | 'info' }

const toasts = ref<Toast[]>([])
let counter = 0

function add(message: string, type: Toast['type'] = 'info', duration = 4000) {
  const id = ++counter
  toasts.value.push({ id, message, type })
  setTimeout(() => remove(id), duration)
}

function remove(id: number) {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

function bgClass(type: string) {
  return { success: 'bg-white border border-green-200', error: 'bg-white border border-red-200', info: 'bg-white border border-blue-200' }[type] || 'bg-white'
}
function iconFor(type: string) {
  return { success: CheckCircleIcon, error: ExclamationCircleIcon, info: InformationCircleIcon }[type] || InformationCircleIcon
}
function iconClass(type: string) {
  return { success: 'text-green-500', error: 'text-red-500', info: 'text-blue-500' }[type] || 'text-slate-500'
}
function textClass(type: string) {
  return { success: 'text-green-800', error: 'text-red-800', info: 'text-blue-800' }[type] || 'text-slate-800'
}

defineExpose({ add })
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>
