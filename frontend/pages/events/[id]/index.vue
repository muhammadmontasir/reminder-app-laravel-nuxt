<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Event Details</h1>
        <div class="flex gap-2">
          <UiBaseButton 
            variant="secondary" 
            @click="navigateTo(`/events/${route.params.id}/edit`)"
          >
            Edit Event
          </UiBaseButton>
          <UiBaseButton 
            variant="danger" 
            @click="handleDelete"
            :disabled="loading"
          >
            {{ loading ? 'Deleting...' : 'Delete Event' }}
          </UiBaseButton>
        </div>
      </div>

      <div v-if="event" class="bg-white shadow rounded-lg p-6">
        <div class="space-y-4">
          <div>
            <h2 class="text-xl font-medium text-gray-900">{{ event.title }}</h2>
            <p class="mt-1 text-gray-600">{{ event.description }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Start Time</h3>
              <p class="mt-1">{{ formatDateTime(event.start_time) }}</p>
            </div>

            <div>
              <h3 class="text-sm font-medium text-gray-500">End Time</h3>
              <p class="mt-1">{{ formatDateTime(event.end_time) }}</p>
            </div>

            <div>
              <h3 class="text-sm font-medium text-gray-500">Reminder Time</h3>
              <p class="mt-1">{{ formatDateTime(event.reminder_time) }}</p>
            </div>

            <div>
              <h3 class="text-sm font-medium text-gray-500">Status</h3>
              <p class="mt-1">{{ event.status }}</p>
            </div>
          </div>

          <div>
            <h3 class="text-sm font-medium text-gray-500">Participants</h3>
            <ul class="mt-2 space-y-1">
              <li v-for="participant in event.participants" :key="participant" class="text-gray-600">
                {{ participant }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-600">{{ error }}</p>
        <UiBaseButton 
          variant="secondary" 
          class="mt-4"
          @click="navigateTo('/')"
        >
          Back to Events
        </UiBaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const api = useApi()
const toast = useToast()
const loading = ref(false)
const event = ref<Event | null>(null)
const error = ref<string | null>(null)

const formatDateTime = (dateString: string) => {
  if (!dateString) return ''
  
  const utcDate = new Date(dateString)
  const localDate = new Date(utcDate.getTime() + utcDate.getTimezoneOffset() * 60000)
  return localDate.toISOString().slice(0, 19).replace('T', ' ')
}

const fetchEvent = async () => {
  try {
    const response = await api.get(`/events/${route.params.id}`)
    event.value = response.data
  } catch (err) {
    error.value = 'Failed to load event details'
    toast.error('Failed to load event details')
  }
}

const handleDelete = async () => {
  if (!confirm('Are you sure you want to delete this event?')) return

  try {
    loading.value = true
    await api.delete(`/events/${route.params.id}`)
    toast.success('Event deleted successfully')
    navigateTo('/')
  } catch (err) {
    toast.error('Failed to delete event')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchEvent()
})

definePageMeta({
  middleware: ['auth']
})
</script> 