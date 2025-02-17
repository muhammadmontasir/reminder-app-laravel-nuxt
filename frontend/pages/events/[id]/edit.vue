<template>
    <div class="py-6">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-semibold text-gray-900">Edit Event</h1>
          <UiBaseButton 
            variant="primary" 
            @click="navigateTo(`/events/${route.params.id}`)" 
            class="bg-gray-50 text-gray-600"
          >
            Back to Event
          </UiBaseButton>
        </div>
        
        <div v-if="event" class="mt-6 bg-white rounded-lg shadow-sm p-6">
          <EventsEventForm 
            :initial-data="event" 
            :is-edit="true"
            @success="navigateTo(`/events/${route.params.id}`)" 
          />
        </div>
  
        <div v-else-if="loading" class="mt-6 text-center text-gray-500">
          Loading...
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  definePageMeta({
    layout: 'default',
    middleware: ['auth']
  })
  
  const route = useRoute()
  const event = ref<any>(null)
  const loading = ref(true)
  const api = useApi()
  const toast = useToast()
  
  const fetchEvent = async () => {
    try {
      loading.value = true
      const response = await api.get(`/events/${route.params.id}`)
      event.value = response.data
    } catch (error) {
      toast.error('Failed to load event')
      navigateTo('/')
    } finally {
      loading.value = false
    }
  }
  
  onMounted(fetchEvent)
  </script>