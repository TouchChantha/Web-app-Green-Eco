<template>
  <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-start gap-4 hover:shadow-md transition-shadow">
    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0', iconBg]">
      <component :is="icon" :class="['w-6 h-6', iconColor]" />
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm text-slate-500 font-medium">{{ label }}</p>
      <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ value }}</p>
      <p v-if="sub" class="text-xs text-slate-400 mt-0.5">{{ sub }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  value: string | number
  sub?: string
  icon: any
  iconBg?: string
  iconColor?: string
}>()
</script>
