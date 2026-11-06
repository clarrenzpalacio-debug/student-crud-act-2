import { ref, computed } from 'vue'

// Module-level reactive singleton state (shared across all consumers)
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

/**
 * Clear local reactive and persistent authentication storage
 * without triggering a backend network request.
 */
function clearAuthState() {
  token.value = ''
  currentUser.value = null
  localStorage.removeItem('api_token')
  localStorage.removeItem('api_user')
}

/**
 * Set the authentication state after a successful response
 */
function setAuthState(newToken, newUser) {
  token.value = newToken
  currentUser.value = newUser
  localStorage.setItem('api_token', newToken)
  localStorage.setItem('api_user', JSON.stringify(newUser))
}

/**
 * Login using credentials against POST /api/login
 */
async function login(credentials) {
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
      const error = new Error(data.errors[firstField][0] || 'Validation error.')
      error.status = 422
      error.errors = data.errors
      throw error
    }
    const error = new Error(data.message || 'Login failed. Please check your credentials.')
    error.status = res.status
    throw error
  }

  setAuthState(data.token, data.user)
  return data
}

/**
 * Logout from backend using the current Bearer token,
 * then clear local authentication state.
 */
async function logout() {
  const currentToken = token.value

  if (currentToken) {
    try {
      await fetch('/api/logout', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${currentToken}`,
          Accept: 'application/json',
        },
      })
    } catch (err) {
      // Ignore network errors on logout request
      console.warn('Logout network notice:', err)
    }
  }

  clearAuthState()
}

export function useAuth() {
  return {
    token,
    currentUser,
    isAuthenticated,
    isAdmin,
    maskedToken,
    login,
    logout,
    clearAuthState,
    setAuthState,
  }
}