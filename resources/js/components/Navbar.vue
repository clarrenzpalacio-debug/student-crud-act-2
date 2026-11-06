<template>
  <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <!-- Title & Branding -->
      <div>
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
            SM
          </span>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight">{{ title }}</h1>
        </div>
        <p v-if="subtitle" class="text-xs text-slate-500 mt-1 pl-10">{{ subtitle }}</p>
      </div>

      <!-- Authenticated User Profile & Logout Section -->
      <div v-if="user" class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl p-2.5 sm:px-4">
        <!-- Initials Avatar -->
        <div class="w-9 h-9 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
          {{ userInitials }}
        </div>

        <!-- User Information & Role Badge -->
        <div class="text-left pr-2 border-r border-slate-200">
          <p class="text-xs font-bold text-slate-800 leading-tight">{{ user.name }}</p>
          <div class="flex items-center gap-1.5 mt-0.5">
            <span
              class="px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded-full"
              :class="user.role === 'admin' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'"
            >
              {{ user.role }}
            </span>
            <span class="text-[10px] text-slate-400 font-mono hidden sm:inline">{{ user.email }}</span>
          </div>
        </div>

        <!-- Logout Action Button -->
        <button
          type="button"
          @click="$emit('logout')"
          :disabled="logoutLoading"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 rounded-lg transition-colors disabled:opacity-50"
          title="Revoke Bearer token and sign out"
        >
          <svg v-if="logoutLoading" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>{{ logoutLoading ? 'Signing out...' : 'Sign Out' }}</span>
        </button>
      </div>
    </div>
  </header>
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
  logoutLoading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['logout'])

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
