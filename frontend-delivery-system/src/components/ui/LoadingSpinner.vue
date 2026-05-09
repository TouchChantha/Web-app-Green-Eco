<template>
  <div class="flex items-center justify-center" :class="fullPage ? 'min-h-64' : ''">
    <div class="flex flex-col items-center gap-3">
      <svg class="animate-spin" :class="sizeClass" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="#3d9a3d" stroke-width="4"/>
        <path class="opacity-75" fill="#3d9a3d" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
      </svg>
      <p v-if="text" class="text-sm text-slate-500">{{ text }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ size?: 'sm' | 'md' | 'lg'; text?: string; fullPage?: boolean }>()
const sizeClass = computed(() => ({
  sm: 'w-5 h-5',
  md: 'w-8 h-8',
  lg: 'w-12 h-12',
}[props.size || 'md']))
</script>
