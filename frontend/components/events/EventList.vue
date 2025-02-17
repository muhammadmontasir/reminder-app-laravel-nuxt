<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th v-for="header in headers" :key="header.key" class="px-4 py-3 text-left text-sm font-medium text-gray-600">
            {{ header.label }}
          </th>
          <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="event in events" :key="event.event_id" class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm font-medium text-gray-900">
            {{ event.event_id }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-900">
            {{ event.title }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-500">
            {{ event.description }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-500">
            {{ formatDate(event.start_time) }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-500">
            {{ formatDate(event.end_time) }}
          </td>
          <td class="px-4 py-3 text-sm">
            <span :class="[
              'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
              event.status === 'upcoming' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'
            ]">
              {{ event.status }}
            </span>
          </td>
          <td class="px-4 py-3 text-sm text-gray-500">
            {{ event.reminder_time ? formatDate(event.reminder_time) : '-' }}
          </td>
          <td class="px-4 py-3 text-right text-sm">
            <div class="flex justify-end gap-2">
              <BaseButton
                variant="secondary"
                @click="navigateTo(`/events/${event.event_id}/edit`)"
              >
                Edit
              </BaseButton>
              <BaseButton
                variant="danger"
                @click="handleDelete(event.event_id)"
              >
                Delete
              </BaseButton>
            </div>
          </td>
        </tr>
        <tr v-if="!events.length">
          <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
            No events found
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { log } from 'console'

interface Event {
  event_id: string
  title: string
  description: string
  start_time: string
  end_time: string
  status: string
  reminder_time: string | null
}

const headers = [
  { key: 'event_id', label: 'Event ID' },
  { key: 'title', label: 'Title' },
  { key: 'description', label: 'Description' },
  { key: 'start_time', label: 'Start Time' },
  { key: 'end_time', label: 'End Time' },
  { key: 'status', label: 'Status' },
  { key: 'reminder_time', label: 'Reminder Time' }
]

const events = ref<Event[]>([])
const api = useApi()
const toast = useToast()

const fetchEvents = async () => {
  try {
    const response = await api.get('/events')
    console.log('Response:', response)
    events.value = response?.data
  } catch (error) {
    console.error('Error fetching events:', error)
    toast.error('Failed to fetch events')
  }
}

const handleDelete = async (eventId: string) => {
  if (!confirm('Are you sure you want to delete this event?')) return

  try {
    await api.delete(`/events/${eventId}`)
    toast.success('Event deleted successfully')
    await fetchEvents()
  } catch (error) {
    toast.error('Failed to delete event')
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(fetchEvents)
</script> 