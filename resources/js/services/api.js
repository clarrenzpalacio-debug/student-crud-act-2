import { useAuth } from '../composables/useAuth'

/**
 * Custom error class wrapping API response details
 */
export class ApiError extends Error {
  constructor(message, status = 500, errors = null, data = null) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
    this.data = data
  }
}

/**
 * Core HTTP request handler
 */
async function request(endpoint, options = {}) {
  const { token, clearAuthState } = useAuth()

  const config = {
    method: options.method || 'GET',
    headers: {
      Accept: 'application/json',
      ...(options.headers || {}),
    },
  }

  // Attach Content-Type for request bodies
  if (options.body && !(options.body instanceof FormData)) {
    config.headers['Content-Type'] = 'application/json'
    config.body = typeof options.body === 'string' ? options.body : JSON.stringify(options.body)
  } else if (options.body) {
    config.body = options.body
  }

  // Automatically attach Bearer token if present
  if (token.value) {
    config.headers['Authorization'] = `Bearer ${token.value}`
  }

  const response = await fetch(endpoint, config)

  let data = null
  const contentType = response.headers.get('content-type')
  if (contentType && contentType.includes('application/json')) {
    try {
      data = await response.json()
    } catch {
      data = null
    }
  }

  // Handle 401 Unauthorized: token expired or invalid
  if (response.status === 401) {
    // Immediately clear local authentication state WITHOUT calling the backend logout
    clearAuthState()
    const message = data?.message || 'Your session has expired. Please sign in again.'
    throw new ApiError(message, 401, null, data)
  }

  // Handle other error responses (403, 422, 500, etc.)
  if (!response.ok) {
    const message = data?.message || `Request failed with status ${response.status}`
    const validationErrors = response.status === 422 ? data?.errors : null
    throw new ApiError(message, response.status, validationErrors, data)
  }

  return data
}

/**
 * Centralized API client methods
 */
export const api = {
  get(endpoint, options = {}) {
    return request(endpoint, { ...options, method: 'GET' })
  },

  post(endpoint, body, options = {}) {
    return request(endpoint, { ...options, method: 'POST', body })
  },

  put(endpoint, body, options = {}) {
    return request(endpoint, { ...options, method: 'PUT', body })
  },

  delete(endpoint, options = {}) {
    return request(endpoint, { ...options, method: 'DELETE' })
  },
}