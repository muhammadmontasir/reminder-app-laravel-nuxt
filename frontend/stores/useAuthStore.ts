import { defineStore } from 'pinia'
import { useLocalStorage } from '@vueuse/core'

interface User {
  id: number
  name: string
  email: string
}

interface LoginForm {
  email: string
  password: string
}

interface RegisterForm {
  name: string
  email: string
  password: string
  password_confirmation: string
}

interface AuthResponse {
  token: string
  user: User
}

export const useAuthStore = defineStore('auth', () => {
  const user = useLocalStorage<User | null>('user', null)
  const token = useLocalStorage<string | null>('token', null)
  const isInitialized = ref(false)
  const api = useApi()
  const config = useRuntimeConfig()

  const initCsrf = async () => {
    try {
      await fetch(`${config.public.apiBase.replace('/api/v1', '')}/sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include',
      })
    } catch (error) {
      console.error('Failed to get CSRF cookie:', error)
      throw error
    }
  }

  const login = async (form: LoginForm) => {
    await initCsrf()
    const response = await api.post('/login', form)
    token.value = response.token
    user.value = response.user
  }

  const register = async (form: RegisterForm) => {
    await initCsrf()
    const response = await api.post('/register', form)
    token.value = response.token
    user.value = response.user
    navigateTo('/login')
  }

  const logout = async () => {
    if (token.value) {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      }
    }
    user.value = null
    token.value = null
    navigateTo('/login')
  }

  const fetchUser = async () => {
    if (!token.value) return null
    
    try {
      const response = await api.get('/user')
      user.value = response.data
      return response.data
    } catch (error) {
      console.error('Fetch user error:', error)
      await logout()
      return null
    }
  }

  const init = async () => {
    if (isInitialized.value) return
    
    if (token.value && user.value) {
      try {
        await fetchUser()
      } catch (error) {
        console.error('Init error:', error)
        await logout()
      }
    } else if (!token.value && !user.value) {
      const route = useRoute()
      if (!route.path.includes('login') && !route.path.includes('register')) {
        navigateTo('/login')
      }
    }
    
    isInitialized.value = true
  }

  return {
    user,
    token,
    login,
    register,
    logout,
    fetchUser,
    init
  }
}) 