<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden transition-all transform animate-in fade-in zoom-in-95 duration-150">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
        <div>
          <h3 class="text-lg font-bold text-slate-800">
            {{ isEditing ? 'Edit Student Record' : 'Add New Student' }}
          </h3>
          <p class="text-xs text-slate-500 mt-0.5">
            {{ isEditing ? 'Update student information and save changes.' : 'Fill out the details below to register a student.' }}
          </p>
        </div>
        <button
          type="button"
          @click="$emit('close')"
          :disabled="loading"
          class="text-slate-400 hover:text-slate-600 rounded-lg p-1 hover:bg-slate-200/60 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- General Error banner -->
      <div v-if="serverError" class="mx-6 mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2 text-rose-700 text-xs">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>{{ serverError }}</span>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <!-- Student ID -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">
            Student ID <span class="text-rose-500">*</span>
          </label>
          <input
            v-model.trim="form.Student_id"
            type="text"
            placeholder="e.g. 2024-0001"
            class="w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors font-mono"
            :class="errors.Student_id ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300'"
          />
          <p v-if="errors.Student_id" class="text-xs text-rose-600 mt-1 font-medium">
            {{ errors.Student_id[0] || errors.Student_id }}
          </p>
        </div>

        <!-- Name Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              First Name <span class="text-rose-500">*</span>
            </label>
            <input
              v-model.trim="form.first_name"
              type="text"
              placeholder="e.g. Juan"
              class="w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
              :class="errors.first_name ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300'"
            />
            <p v-if="errors.first_name" class="text-xs text-rose-600 mt-1 font-medium">
              {{ errors.first_name[0] || errors.first_name }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Last Name <span class="text-rose-500">*</span>
            </label>
            <input
              v-model.trim="form.last_name"
              type="text"
              placeholder="e.g. Dela Cruz"
              class="w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
              :class="errors.last_name ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300'"
            />
            <p v-if="errors.last_name" class="text-xs text-rose-600 mt-1 font-medium">
              {{ errors.last_name[0] || errors.last_name }}
            </p>
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">
            Email Address <span class="text-rose-500">*</span>
          </label>
          <input
            v-model.trim="form.email"
            type="email"
            placeholder="e.g. juan@example.com"
            class="w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
            :class="errors.email ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300'"
          />
          <p v-if="errors.email" class="text-xs text-rose-600 mt-1 font-medium">
            {{ errors.email[0] || errors.email }}
          </p>
        </div>

        <!-- Course & Year Level -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Course / Program <span class="text-rose-500">*</span>
            </label>
            <input
              v-model.trim="form.course"
              type="text"
              placeholder="e.g. BSIT"
              class="w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
              :class="errors.course ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300'"
            />
            <p v-if="errors.course" class="text-xs text-rose-600 mt-1 font-medium">
              {{ errors.course[0] || errors.course }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Year Level <span class="text-rose-500">*</span>
            </label>
            <select
              v-model.number="form.year_level"
              class="w-full px-3 py-2 text-sm bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
              :class="errors.year_level ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300'"
            >
              <option value="" disabled>Select Year Level</option>
              <option :value="1">1st Year</option>
              <option :value="2">2nd Year</option>
              <option :value="3">3rd Year</option>
              <option :value="4">4th Year</option>
              <option :value="5">5th Year</option>
            </select>
            <p v-if="errors.year_level" class="text-xs text-rose-600 mt-1 font-medium">
              {{ errors.year_level[0] || errors.year_level }}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
          <button
            type="button"
            @click="$emit('close')"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors disabled:opacity-50"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Student') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  student: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  serverErrors: {
    type: Object,
    default: () => ({}),
  },
  serverError: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['close', 'submit'])

const isEditing = ref(false)
const errors = ref({})

const form = reactive({
  Student_id: '',
  first_name: '',
  last_name: '',
  email: '',
  course: '',
  year_level: '',
})

watch(
  () => props.student,
  (newVal) => {
    errors.value = {}
    if (newVal) {
      isEditing.value = true
      form.Student_id = newVal.Student_id || ''
      form.first_name = newVal.first_name || ''
      form.last_name = newVal.last_name || ''
      form.email = newVal.email || ''
      form.course = newVal.course || ''
      form.year_level = newVal.year_level || ''
    } else {
      isEditing.value = false
      form.Student_id = ''
      form.first_name = ''
      form.last_name = ''
      form.email = ''
      form.course = ''
      form.year_level = ''
    }
  },
  { immediate: true }
)

watch(
  () => props.serverErrors,
  (newErrors) => {
    errors.value = { ...(newErrors || {}) }
  },
  { deep: true }
)

const validateClientSide = () => {
  const errs = {}
  if (!form.Student_id) errs.Student_id = 'Student ID is required.'
  if (!form.first_name) errs.first_name = 'First name is required.'
  if (!form.last_name) errs.last_name = 'Last name is required.'
  if (!form.email) {
    errs.email = 'Email address is required.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errs.email = 'Enter a valid email address.'
  }
  if (!form.course) errs.course = 'Course is required.'
  if (!form.year_level) {
    errs.year_level = 'Year level is required.'
  } else if (Number(form.year_level) < 1 || Number(form.year_level) > 5) {
    errs.year_level = 'Year level must be between 1 and 5.'
  }

  errors.value = errs
  return Object.keys(errs).length === 0
}

const handleSubmit = () => {
  if (!validateClientSide()) return
  emit('submit', {
    isEditing: isEditing.value,
    id: props.student ? props.student.id : null,
    data: {
      Student_id: form.Student_id,
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      course: form.course,
      year_level: Number(form.year_level),
    },
  })
}
</script>

