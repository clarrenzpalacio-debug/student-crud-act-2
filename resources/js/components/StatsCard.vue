<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div
      v-for="stat in stats"
      :key="stat.label"
      class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between"
    >
      <div>
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ stat.label }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ stat.value }}</p>
      </div>
      <div
        class="w-10 h-10 rounded-lg flex items-center justify-center text-lg font-bold"
        :class="stat.colorClass || 'bg-indigo-50 text-indigo-600'"
      >
        {{ stat.icon || '#' }}
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Array,
    required: true,
    default: () => [],
  },
})
</script>

