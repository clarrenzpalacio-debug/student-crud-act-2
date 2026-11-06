<template>
  <div class="min-h-screen bg-slate-100 text-slate-800 p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
      <!-- Navbar Component showing User Profile & Logout -->
      <Navbar
        title="Student Management System"
        subtitle="Laravel Sanctum Bearer Authentication • Role-Based Access Control"
        :user="currentUser"
        :logout-loading="logoutLoading"
        @logout="handleLogout"
      />

      <!-- Success Notification Banner -->
      <div
        v-if="successMessage"
        class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm animate-in fade-in slide-in-from-top-2 duration-150"
      >
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-medium">{{ successMessage }}</span>
        </div>
        <button
          type="button"
          @click="successMessage = null"
          class="text-emerald-500 hover:text-emerald-700 p-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Error Notification Banner -->
      <div
        v-if="globalError"
        class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between shadow-sm animate-in fade-in slide-in-from-top-2 duration-150"
      >
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-medium">{{ globalError }}</span>
        </div>
        <button
          type="button"
          @click="globalError = null"
          class="text-rose-500 hover:text-rose-700 p-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Vue Login Interface (when not authenticated) -->
      <LoginForm
        v-if="!isAuthenticated"
        :loading="authLoading"
        :error-message="authError"
        @login="handleLogin"
      />

      <!-- Protected Content (when authenticated with Bearer token) -->
      <div v-else>
        <!-- Authenticated Token & Role Status Banner -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3.5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
          <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
            <span class="text-slate-700 font-medium">
              Sanctum Bearer Token Active • User:
              <strong class="text-indigo-900 font-bold">{{ currentUser?.name }}</strong>
              <span
                class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                :class="isAdmin ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'"
              >
                {{ currentUser?.role }}
              </span>
              <span v-if="isAdmin" class="text-emerald-700 font-semibold ml-1.5">
                (Full CRUD Access)
              </span>
              <span v-else class="text-slate-500 font-medium ml-1.5">
                (Read-Only: Admin required for Add/Edit/Delete)
              </span>
            </span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-[11px] text-slate-400 font-mono">Token: {{ maskedToken }}</span>
            <button
              type="button"
              @click="handleLogout"
              :disabled="logoutLoading"
              class="text-xs font-semibold text-indigo-700 hover:text-indigo-900 underline"
            >
              Revoke Token
            </button>
          </div>
        </div>

        <!-- Dashboard Stats Cards -->
        <StatsCard :stats="statCardsData" />

        <!-- Student Records Management Table -->
        <StudentList
          :students="filteredStudents"
          :loading="loading"
          :error="errorMessage"
          :course-options="courseOptions"
          :can-manage="isAdmin"
          v-model:searchQuery="searchQuery"
          v-model:selectedCourse="selectedCourse"
          @refresh="fetchStudents"
          @create="openCreateModal"
          @edit="openEditModal"
          @delete="openDeleteModal"
        />

        <!-- Create / Edit Student Modal -->
        <StudentModal
          :is-open="isFormModalOpen"
          :student="selectedStudent"
          :loading="formSubmitting"
          :server-errors="formErrors"
          :server-error="modalError"
          @close="closeFormModal"
          @submit="handleSaveStudent"
        />

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
          :is-open="isDeleteModalOpen"
          :student="studentToDelete"
          :loading="deleteLoading"
          :error="deleteError"
          @close="closeDeleteModal"
          @confirm="handleConfirmDelete"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Navbar from './components/Navbar.vue'
import LoginForm from './components/LoginForm.vue'
import StatsCard from './components/StatsCard.vue'
import StudentList from './components/StudentList.vue'
import StudentModal from './components/StudentModal.vue'
import ConfirmDeleteModal from './components/ConfirmDeleteModal.vue'

// Authentication State
const token = ref(localStorage.getItem('api_token') || '')
const currentUser = ref(
  localStorage.getItem('api_user')
    ? JSON.parse(localStorage.getItem('api_user'))
    : null
)
const isAuthenticated = computed(() => Boolean(token.value))
const isAdmin = computed(() => currentUser.value?.role === 'admin')
const maskedToken = computed(() => {
  if (!token.value) return ''
  return token.value.slice(0, 10) + '...' + token.value.slice(-6)
})

const authLoading = ref(false)
const authError = ref(null)
const logoutLoading = ref(false)

// Student Data State
const students = ref([])
const loading = ref(false)
const errorMessage = ref(null)
const searchQuery = ref('')
const selectedCourse = ref('')

// Toast Notification Banners
const successMessage = ref(null)
const globalError = ref(null)

const showSuccess = (msg) => {
  successMessage.value = msg
  globalError.value = null
  setTimeout(() => {
    if (successMessage.value === msg) {
      successMessage.value = null
    }
  }, 4000)
}

const showError = (msg) => {
  globalError.value = msg
  setTimeout(() => {
    if (globalError.value === msg) {
      globalError.value = null
    }
  }, 5000)
}

/**
 * Connect to existing Laravel /api/login endpoint
 * Validates credentials, retrieves token and user info, stores them in localStorage
 */
const handleLogin = async (credentials) => {
  authLoading.value = true
  authError.value = null

  try {
    const res = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(credentials),
    })

    const data = await res.json()

    if (!res.ok) {
      if (res.status === 422 && data.errors) {
        const firstField = Object.keys(data.errors)[0]
        throw new Error(data.errors[firstField][0] || 'Validation error.')
      }
      throw new Error(data.message || 'Login failed. Please check your credentials.')
    }

    // Store retrieved Bearer token and user info with role
    token.value = data.token
    currentUser.value = data.user
    localStorage.setItem('api_token', data.token)
    localStorage.setItem('api_user', JSON.stringify(data.user))

    showSuccess(`Signed in successfully as ${data.user.name} (${data.user.role}).`)
    await fetchStudents()
  } catch (err) {
    authError.value = err.message || 'Unable to connect to authentication server.'
  } finally {
    authLoading.value = false
  }
}

/**
 * Connect to existing Laravel /api/logout endpoint
 * Revokes current Bearer token from personal_access_tokens and clears localStorage
 */
const handleLogout = async () => {
  logoutLoading.value = true

  try {
    if (token.value) {
      await fetch('/api/logout', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: 'application/json',
        },
      })
    }
  } catch (err) {
    console.error('Logout error:', err)
  } finally {
    // Clear state & storage regardless
    token.value = ''
    currentUser.value = null
    students.value = []
    localStorage.removeItem('api_token')
    localStorage.removeItem('api_user')
    logoutLoading.value = false
    showSuccess('Token revoked. Signed out successfully.')
  }
}

/**
 * Fetch students using Sanctum Bearer token
 */
const fetchStudents = async () => {
  if (!token.value) {
    errorMessage.value = 'Please sign in to fetch student records.'
    return
  }

  loading.value = true
  errorMessage.value = null

  try {
    const res = await fetch('/api/students', {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json',
      },
    })

    const payload = await res.json()

    if (!res.ok) {
      if (res.status === 401) {
        handleLogout()
        throw new Error('Your session has expired. Please sign in again.')
      }
      throw new Error(payload.message || `API error: ${res.statusText}`)
    }

    if (Array.isArray(payload)) {
      students.value = payload
    } else if (payload && Array.isArray(payload.data)) {
      students.value = payload.data
    } else {
      students.value = []
    }
  } catch (err) {
    errorMessage.value = err.message || 'Failed to connect to the Laravel REST API.'
  } finally {
    loading.value = false
  }
}

// Modal States: Create / Edit
const isFormModalOpen = ref(false)
const selectedStudent = ref(null)
const formSubmitting = ref(false)
const formErrors = ref({})
const modalError = ref(null)

const openCreateModal = () => {
  selectedStudent.value = null
  formErrors.value = {}
  modalError.value = null
  isFormModalOpen.value = true
}

const openEditModal = (student) => {
  selectedStudent.value = { ...student }
  formErrors.value = {}
  modalError.value = null
  isFormModalOpen.value = true
}

const closeFormModal = () => {
  if (formSubmitting.value) return
  isFormModalOpen.value = false
  selectedStudent.value = null
  formErrors.value = {}
  modalError.value = null
}

const handleSaveStudent = async ({ isEditing, id, data }) => {
  formSubmitting.value = true
  formErrors.value = {}
  modalError.value = null

  const endpoint = isEditing ? `/api/students/${id}` : '/api/students'
  const method = isEditing ? 'PUT' : 'POST'

  try {
    const res = await fetch(endpoint, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json',
      },
      body: JSON.stringify(data),
    })

    const result = await res.json()

    if (!res.ok) {
      if (res.status === 422 && result.errors) {
        formErrors.value = result.errors
        throw new Error('Please correct the validation errors in the form.')
      } else if (res.status === 403) {
        throw new Error(result.message || 'Forbidden: Administrator privileges required.')
      } else if (res.status === 401) {
        handleLogout()
        throw new Error('Unauthenticated: Your session has expired. Please sign in again.')
      } else {
        throw new Error(result.message || 'An unexpected error occurred while saving.')
      }
    }

    await fetchStudents()
    closeFormModal()
    showSuccess(
      isEditing
        ? `Student #${data.Student_id} updated successfully.`
        : `Student #${data.Student_id} registered successfully.`
    )
  } catch (err) {
    modalError.value = err.message
  } finally {
    formSubmitting.value = false
  }
}

// Modal States: Delete
const isDeleteModalOpen = ref(false)
const studentToDelete = ref(null)
const deleteLoading = ref(false)
const deleteError = ref(null)

const openDeleteModal = (student) => {
  studentToDelete.value = student
  deleteError.value = null
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (deleteLoading.value) return
  isDeleteModalOpen.value = false
  studentToDelete.value = null
  deleteError.value = null
}

const handleConfirmDelete = async (student) => {
  if (!student || !student.id) return
  deleteLoading.value = true
  deleteError.value = null

  try {
    const res = await fetch(`/api/students/${student.id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json',
      },
    })

    const result = await res.json()

    if (!res.ok) {
      if (res.status === 403) {
        throw new Error(result.message || 'Forbidden: Administrator privileges required.')
      } else {
        throw new Error(result.message || 'Failed to delete student record.')
      }
    }

    await fetchStudents()
    closeDeleteModal()
    showSuccess(`Student ${student.first_name} ${student.last_name} deleted successfully.`)
  } catch (err) {
    deleteError.value = err.message
  } finally {
    deleteLoading.value = false
  }
}

// Derived Courses for dropdown
const courseOptions = computed(() => {
  const set = new Set()
  students.value.forEach((s) => {
    if (s.course) set.add(s.course)
  })
  return Array.from(set).sort()
})

// Filtered student list
const filteredStudents = computed(() => {
  return students.value.filter((student) => {
    const matchesSearch =
      !searchQuery.value ||
      (student.first_name &&
        student.first_name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (student.last_name &&
        student.last_name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (student.email &&
        student.email.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (student.Student_id &&
        student.Student_id.toLowerCase().includes(searchQuery.value.toLowerCase()))

    const matchesCourse =
      !selectedCourse.value || student.course === selectedCourse.value

    return matchesSearch && matchesCourse
  })
})

// Dashboard Stats Data
const statCardsData = computed(() => {
  const total = students.value.length
  const coursesCount = courseOptions.value.length
  const avgYear = total
    ? (
        students.value.reduce((acc, s) => acc + (Number(s.year_level) || 0), 0) /
        total
      ).toFixed(1)
    : '0'

  return [
    {
      label: 'Total Students',
      value: total,
      icon: '👥',
      colorClass: 'bg-indigo-50 text-indigo-600',
    },
    {
      label: 'Unique Courses',
      value: coursesCount,
      icon: '🎓',
      colorClass: 'bg-emerald-50 text-emerald-600',
    },
    {
      label: 'Avg Year Level',
      value: avgYear,
      icon: '📊',
      colorClass: 'bg-amber-50 text-amber-600',
    },
    {
      label: 'Filtered Count',
      value: filteredStudents.value.length,
      icon: '🔍',
      colorClass: 'bg-purple-50 text-purple-600',
    },
  ]
})

onMounted(() => {
  if (token.value) {
    fetchStudents()
  }
})
</script>
