<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ title }}</h1>
        <p v-if="subtitle" class="text-sm text-slate-500 mt-1">{{ subtitle }}</p>
      </div>
      <div v-if="user" class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2">
        <div class="w-9 h-9 rounded-full bg-indigo-600 text-white font-semibold flex items-center justify-center text-sm shadow-sm">
          {{ userInitials }}
        </div>
        <div class="text-left">
          <p class="text-xs font-semibold text-slate-800 leading-tight">{{ user.name }}</p>
          <span
            class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded-full"
            :class="user.role === 'admin' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'"
          >
            {{ user.role }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  user: {
    type: Object,
    default: null,
  },
})

const userInitials = computed(() => {
  if (!props.user || !props.user.name) return '?'
  return props.user.name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})
</script>

