<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header with Search, Filter & Add Student Button -->
    <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
      <div class="flex flex-col sm:flex-row gap-2 flex-1 max-w-lg">
        <!-- Search bar -->
        <div class="relative flex-1">
          <input
            :value="searchQuery"
            @input="$emit('update:searchQuery', $event.target.value)"
            type="text"
            placeholder="Search by name, email, or Student ID..."
            class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
          </svg>
        </div>

        <!-- Course filter -->
        <select
          :value="selectedCourse"
          @change="$emit('update:selectedCourse', $event.target.value)"
          class="px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">All Courses</option>
          <option v-for="course in courseOptions" :key="course" :value="course">
            {{ course }}
          </option>
        </select>
      </div>

      <!-- Actions: Refresh & Add Student Button -->
      <div class="flex items-center gap-2">
        <button
          @click="$emit('refresh')"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm"
          title="Refresh student list"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </button>

        <button
          v-if="canManage"
          @click="$emit('create')"
          class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Student
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-100/75 text-xs font-semibold text-slate-600 uppercase tracking-wider">
          <tr>
            <th scope="col" class="py-3.5 px-4">Student ID</th>
            <th scope="col" class="py-3.5 px-4">Full Name</th>
            <th scope="col" class="py-3.5 px-4">Email</th>
            <th scope="col" class="py-3.5 px-4">Course</th>
            <th scope="col" class="py-3.5 px-4 text-center">Year Level</th>
            <th scope="col" class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
          <!-- Loading state with v-if -->
          <tr v-if="loading">
            <td colspan="6" class="py-12 text-center text-slate-500">
              <div class="inline-flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Loading student records from API...</span>
              </div>
            </td>
          </tr>

          <!-- Error state with v-else-if -->
          <tr v-else-if="error">
            <td colspan="6" class="py-8 text-center text-rose-600 bg-rose-50/50">
              <p class="font-medium">{{ error }}</p>
              <button
                @click="$emit('refresh')"
                class="mt-2 text-xs font-semibold text-rose-700 underline hover:text-rose-900"
              >
                Try Again
              </button>
            </td>
          </tr>

          <!-- Empty state with v-else-if -->
          <tr v-else-if="students.length === 0">
            <td colspan="6" class="py-12 text-center text-slate-400">
              <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <p class="text-base font-semibold text-slate-600">No students found</p>
              <p class="text-xs text-slate-400 mt-1">There are no matching student records in the database.</p>
              <button
                v-if="canManage"
                @click="$emit('create')"
                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition-colors"
              >
                + Register First Student
              </button>
            </td>
          </tr>

          <!-- Data rows with v-else and v-for -->
          <tr
            v-else
            v-for="(student, index) in students"
            :key="student.id || index"
            class="hover:bg-indigo-50/40 transition-colors group"
          >
            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-indigo-700">
              {{ student.Student_id || 'N/A' }}
            </td>
            <td class="py-3.5 px-4 font-medium text-slate-900">
              {{ student.first_name }} {{ student.last_name }}
            </td>
            <td class="py-3.5 px-4 text-slate-600">
              <a :href="'mailto:' + student.email" class="hover:text-indigo-600 underline">
                {{ student.email }}
              </a>
            </td>
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                {{ student.course }}
              </span>
            </td>
            <td class="py-3.5 px-4 text-center">
              <span
                class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold"
                :class="getYearBadgeClass(student.year_level)"
              >
                {{ student.year_level }}
              </span>
            </td>
            <td class="py-3.5 px-4 text-right">
              <!-- Admin actions -->
              <div v-if="canManage" class="inline-flex items-center gap-1.5">
                <button
                  type="button"
                  @click="$emit('edit', student)"
                  class="px-2.5 py-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-md transition-colors"
                  title="Edit record"
                >
                  Edit
                </button>
                <button
                  type="button"
                  @click="$emit('delete', student)"
                  class="px-2.5 py-1 text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition-colors"
                  title="Delete record"
                >
                  Delete
                </button>
              </div>
              <!-- Read-only badge for regular users -->
              <span v-else class="text-[11px] text-slate-400 font-medium italic">
                Read-only
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer showing count -->
    <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex items-center justify-between">
      <span>
        Showing <strong class="font-semibold text-slate-700">{{ students.length }}</strong> student record(s)
      </span>
      <span class="text-[11px] text-slate-400">
        Milestone 2 • Full Student CRUD
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  students: {
    type: Array,
    required: true,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
  searchQuery: {
    type: String,
    default: '',
  },
  selectedCourse: {
    type: String,
    default: '',
  },
  courseOptions: {
    type: Array,
    default: () => [],
  },
  canManage: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['update:searchQuery', 'update:selectedCourse', 'refresh', 'create', 'edit', 'delete'])

const getYearBadgeClass = (year) => {
  const level = Number(year)
  switch (level) {
    case 1:
      return 'bg-blue-100 text-blue-700'
    case 2:
      return 'bg-emerald-100 text-emerald-700'
    case 3:
      return 'bg-amber-100 text-amber-700'
    case 4:
      return 'bg-purple-100 text-purple-700'
    default:
      return 'bg-slate-100 text-slate-700'
  }
}
</script>
