import type { UseFetchOptions } from 'nuxt/app'

export const useApi = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()
  
  const getHeaders = () => {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }

    if (authStore.token) {
      headers['Authorization'] = `Bearer ${authStore.token}`
    }

    return headers
  }

  const fetchOptions: UseFetchOptions<any> = {
    baseURL: config.public.apiBase,
    credentials: 'include'
  }

  return {
    async get(endpoint: string) {
      const { data, error } = await useFetch(endpoint, {
        ...fetchOptions,
        method: 'GET',
        headers: getHeaders()
      })
      
      if (error.value) throw error.value
      return data.value
    },

    async post(endpoint: string, body?: any) {
      const { data, error } = await useFetch(endpoint, {
        ...fetchOptions,
        method: 'POST',
        body,
        headers: getHeaders()
      })
      
      if (error.value) throw error.value
      return data.value
    },

    async put(endpoint: string, body?: any) {
      const { data, error } = await useFetch(endpoint, {
        ...fetchOptions,
        method: 'PUT',
        body,
        headers: getHeaders()
      })
      
      if (error.value) throw error.value
      return data.value
    },

    async delete(endpoint: string) {
      const { error } = await useFetch(endpoint, {
        ...fetchOptions,
        method: 'DELETE',
        headers: getHeaders()
      })
      
      if (error.value) throw error.value
    }
  }
} 