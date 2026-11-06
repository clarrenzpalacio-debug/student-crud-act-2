<template>
  <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8 max-w-md w-full mx-auto my-6">
    <!-- Header -->
    <div class="text-center mb-6">
      <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-indigo-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-slate-800">Sign in to your account</h2>
      <p class="text-xs text-slate-500 mt-1">
        Laravel Sanctum Token Authentication & Role-Based Access Control
      </p>
    </div>

    <!-- Error Alert Banner -->
    <div
      v-if="errorMessage"
      class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2.5 text-rose-700 text-xs animate-in fade-in duration-150"
    >
      <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <span class="font-medium leading-relaxed">{{ errorMessage }}</span>
    </div>

    <!-- Login Form -->
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Email Field -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
        <div class="relative">
          <input
            v-model.trim="form.email"
            type="email"
            required
            autocomplete="email"
            placeholder="e.g. admin@example.com"
            :disabled="loading"
            class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors disabled:bg-slate-50 disabled:cursor-not-allowed"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
          </svg>
        </div>
      </div>

      <!-- Password Field -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
        <div class="relative">
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            :disabled="loading"
            class="w-full pl-9 pr-10 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors disabled:bg-slate-50 disabled:cursor-not-allowed"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 focus:outline-none"
            tabindex="-1"
          >
            <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ loading ? 'Authenticating...' : 'Sign In' }}</span>
      </button>
    </form>

    <!-- Quick Seed Accounts Selection -->
    <div class="mt-6 pt-5 border-t border-slate-200">
      <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-center mb-3">
        Quick-Fill Seeded Accounts
      </p>
      <div class="grid grid-cols-2 gap-2">
        <button
          type="button"
          @click="fillAccount('admin@example.com', 'password')"
          :disabled="loading"
          class="p-2.5 text-left border border-slate-200 hover:border-indigo-300 rounded-lg bg-slate-50 hover:bg-indigo-50/50 transition-colors"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800">Admin</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">admin</span>
          </div>
          <p class="text-[11px] text-slate-500 font-mono truncate mt-0.5">admin@example.com</p>
        </button>

        <button
          type="button"
          @click="fillAccount('user@example.com', 'password')"
          :disabled="loading"
          class="p-2.5 text-left border border-slate-200 hover:border-indigo-300 rounded-lg bg-slate-50 hover:bg-indigo-50/50 transition-colors"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800">Regular User</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">user</span>
          </div>
          <p class="text-[11px] text-slate-500 font-mono truncate mt-0.5">user@example.com</p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['login'])

const showPassword = ref(false)

const form = reactive({
  email: 'admin@example.com',
  password: 'password',
})

const fillAccount = (email, password) => {
  form.email = email
  form.password = password
}

const handleSubmit = () => {
  emit('login', {
    email: form.email,
    password: form.password,
  })
}
</script>

