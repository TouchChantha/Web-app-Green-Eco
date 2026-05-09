<template>
  <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', colorClass]">
    <span class="w-1.5 h-1.5 rounded-full" :class="dotClass"></span>
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ status: string; type?: 'order' | 'driver' }>()

const config: Record<string, { label: string; color: string; dot: string }> = {
  // Order statuses
  pending:    { label: 'Pending',    color: 'bg-amber-100 text-amber-700',   dot: 'bg-amber-500' },
  assigned:   { label: 'Assigned',   color: 'bg-blue-100 text-blue-700',     dot: 'bg-blue-500' },
  in_transit: { label: 'In Transit', color: 'bg-purple-100 text-purple-700', dot: 'bg-purple-500' },
  delivered:  { label: 'Delivered',  color: 'bg-green-100 text-green-700',   dot: 'bg-green-500' },
  failed:     { label: 'Failed',     color: 'bg-red-100 text-red-700',       dot: 'bg-red-500' },
  cancelled:  { label: 'Cancelled',  color: 'bg-slate-100 text-slate-600',   dot: 'bg-slate-400' },
  // Driver statuses
  available:    { label: 'Available',    color: 'bg-green-100 text-green-700',   dot: 'bg-green-500' },
  on_delivery:  { label: 'On Delivery',  color: 'bg-blue-100 text-blue-700',     dot: 'bg-blue-500' },
  offline:      { label: 'Offline',      color: 'bg-slate-100 text-slate-600',   dot: 'bg-slate-400' },
}

const current = computed(() => config[props.status] || { label: props.status, color: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' })
const colorClass = computed(() => current.value.color)
const dotClass = computed(() => current.value.dot)
const label = computed(() => current.value.label)
</script>
