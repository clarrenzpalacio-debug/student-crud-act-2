<template>
  <div class="min-h-screen bg-slate-100 text-slate-800 p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
      <!-- Navbar Component Demonstrating Props -->
      <Navbar
        title="Student Management System"
        subtitle="Final Lab Activity 3 • Vue.js 3 + Laravel REST API"
        :user="currentUser"
      />

      <!-- Authentication Section if Sanctum Token needed -->
      <div v-if="!isAuthenticated" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-lg font-bold text-slate-800">API Authentication</h2>
            <p class="text-xs text-slate-500">
              Sign in with your seeded credentials to query the protected <code class="bg-slate-100 px-1 py-0.5 rounded font-mono text-indigo-600">/api/students</code> endpoint.
            </p>
          </div>
          <div class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full font-medium">
            Sanctum Protected
          </div>
        </div>

        <form @submit.prevent="handleLogin" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
            <input
              v-model="loginForm.email"
              type="email"
              required
              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="admin@example.com"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
            <input
              v-model="loginForm.password"
              type="password"
              required
              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="password"
            />
          </div>
          <div class="flex items-end gap-2">
            <button
              type="submit"
              :disabled="authLoading"
              class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2 px-4 rounded-lg shadow-sm transition-colors disabled:opacity-50"
            >
              <span v-if="authLoading">Signing In...</span>
              <span v-else>Sign In</span>
            </button>
            <button
              type="button"
              @click="quickLogin('admin')"
              class="px-3 py-2 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-200"
              title="Auto-fill admin credentials"
            >
              Admin
            </button>
            <button
              type="button"
              @click="quickLogin('user')"
              class="px-3 py-2 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-200"
              title="Auto-fill regular user credentials"
            >
              User
            </button>
          </div>
        </form>

        <p v-if="authError" class="text-xs font-medium text-rose-600 mt-2">
          {{ authError }}
        </p>
      </div>

      <div v-else class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 mb-6 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="text-slate-700 font-medium">
            Authenticated via Sanctum Bearer Token as
            <strong class="text-indigo-900">{{ currentUser?.name }}</strong>
            ({{ currentUser?.role }})
          </span>
        </div>
        <button
          @click="handleLogout"
          class="text-xs font-semibold text-indigo-700 hover:text-indigo-900 underline"
        >
          Sign Out / Clear Token
        </button>
      </div>

      <!-- Stats Cards Component Demonstrating Props & v-for -->
      <StatsCard :stats="statCardsData" />

      <!-- Student List Component Demonstrating Props, v-for, and v-if -->
      <StudentList
        :students="filteredStudents"
        :loading="loading"
        :error="errorMessage"
        :course-options="courseOptions"
        v-model:searchQuery="searchQuery"
        v-model:selectedCourse="selectedCourse"
        @refresh="fetchStudents"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Navbar from './components/Navbar.vue'
import StatsCard from './components/StatsCard.vue'
import StudentList from './components/StudentList.vue'

const students = ref([])
const loading = ref(false)
const errorMessage = ref(null)

const searchQuery = ref('')
const selectedCourse = ref('')

const token = ref(localStorage.getItem('api_token') || '')
const currentUser = ref(
  localStorage.getItem('api_user')
    ? JSON.parse(localStorage.getItem('api_user'))
    : null
)
const isAuthenticated = computed(() => Boolean(token.value))

const loginForm = ref({
  email: 'admin@example.com',
  password: 'password',
})
const authLoading = ref(false)
const authError = ref(null)

const quickLogin = (type) => {
  if (type === 'admin') {
    loginForm.value.email = 'admin@example.com'
    loginForm.value.password = 'password'
  } else {
    loginForm.value.email = 'user@example.com'
    loginForm.value.password = 'password'
  }
}

const handleLogin = async () => {
  authLoading.value = true
  authError.value = null
  try {
    const res = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(loginForm.value),
    })

    const data = await res.json()
    if (!res.ok) {
      throw new Error(data.message || 'Login failed. Please check credentials.')
    }

    token.value = data.token
    currentUser.value = data.user
    localStorage.setItem('api_token', data.token)
    localStorage.setItem('api_user', JSON.stringify(data.user))

    await fetchStudents()
  } catch (err) {
    authError.value = err.message
  } finally {
    authLoading.value = false
  }
}

const handleLogout = async () => {
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
  } catch {
    // Ignore error on network cleanup
  } finally {
    token.value = ''
    currentUser.value = null
    students.value = []
    localStorage.removeItem('api_token')
    localStorage.removeItem('api_user')
  }
}

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
      throw new Error(payload.message || `API error: ${res.statusText}`)
    }

    // Handle both { data: [...] } format and raw array format
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

// Course options derived dynamically from records
const courseOptions = computed(() => {
  const set = new Set()
  students.value.forEach((s) => {
    if (s.course) set.add(s.course)
  })
  return Array.from(set).sort()
})

// Filtered students by search & course filter
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

// Computed stats demonstrating reactive calculations
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

