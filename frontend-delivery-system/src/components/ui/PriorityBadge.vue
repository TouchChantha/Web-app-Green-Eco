<template>
  <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wide', colorClass]">
    {{ priority }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ priority: string }>()

const colors: Record<string, string> = {
  low:    'bg-slate-100 text-slate-500',
  normal: 'bg-sky-100 text-sky-600',
  high:   'bg-orange-100 text-orange-600',
  urgent: 'bg-red-100 text-red-600',
}

const colorClass = computed(() => colors[props.priority] || 'bg-slate-100 text-slate-500')
</script>
