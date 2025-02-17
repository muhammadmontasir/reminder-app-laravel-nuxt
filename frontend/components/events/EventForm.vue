<template>
  <form @submit.prevent="handleSubmit" class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">Title</label>
      <input
        v-model="form.title"
        type="text"
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-300': errors.title }"
      />
      <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Description</label>
      <textarea
        v-model="form.description"
        required
        rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-300': errors.description }"
      />
      <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Start Time</label>
      <input
        v-model="form.start_time"
        type="datetime-local"
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-300': errors.start_time }"
      />
      <p v-if="errors.start_time" class="mt-1 text-sm text-red-600">{{ errors.start_time }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">End Time</label>
      <input
        v-model="form.end_time"
        type="datetime-local"
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-300': errors.end_time }"
      />
      <p v-if="errors.end_time" class="mt-1 text-sm text-red-600">{{ errors.end_time }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Reminder Time</label>
      <input
        v-model="form.reminder_time"
        type="datetime-local"
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-300': errors.reminder_time }"
      />
      <p v-if="errors.reminder_time" class="mt-1 text-sm text-red-600">{{ errors.reminder_time }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Participants (comma-separated emails)</label>
      <input
        v-model="participantsInput"
        type="text"
        required
        placeholder="john@example.com, jane@example.com"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        :class="{ 'border-red-300': errors.participants }"
      />
      <p v-if="errors.participants" class="mt-1 text-sm text-red-600">{{ errors.participants }}</p>
    </div>

    <div class="flex justify-end">
      <BaseButton
        type="submit"
        variant="primary"
        :disabled="loading"
      >
        {{ loading ? (isEdit ? 'Updating...' : 'Creating...') : (isEdit ? 'Update Event' : 'Create Event') }}
      </BaseButton>
    </div>
  </form>
</template>

<script setup lang="ts">
const props = defineProps<{
  initialData?: any
  isEdit?: boolean
}>()

const emit = defineEmits<{
  success: []
}>()

const loading = ref(false)
const errors = ref<Record<string, string>>({})
const participantsInput = ref('')
const api = useApi()
const toast = useToast()

const form = reactive({
  title: '',
  description: '',
  start_time: '',
  end_time: '',
  reminder_time: '',
  participants: [] as string[]
})

const formatDateForInput = (dateString: string) => {
  return dateString ? new Date(dateString).toISOString().slice(0, 16) : ''
}

if (props.initialData) {
  form.title = props.initialData.title
  form.description = props.initialData.description
  form.start_time = formatDateForInput(props.initialData.start_time)
  form.end_time = formatDateForInput(props.initialData.end_time)
  form.reminder_time = formatDateForInput(props.initialData.reminder_time)
  form.participants = props.initialData.participants || []
  participantsInput.value = form.participants.join(', ')
}

watch(participantsInput, (value) => {
  form.participants = value.split(',').map(email => email.trim()).filter(email => email)
})


const formatDateForApi = (date: string) => {
  return date.replace('T', ' ')
}

const handleSubmit = async () => {
  try {
    loading.value = true
    errors.value = {}
    
    const data = {
      ...form,
      start_time: formatDateForApi(form.start_time),
      end_time: formatDateForApi(form.end_time),
      reminder_time: formatDateForApi(form.reminder_time)
    }

    if (props.isEdit) {
      await api.put(`/events/${props.initialData.event_id}`, data)
      toast.success('Event updated successfully')
    } else {
      await api.post('/events', data)
      toast.success('Event created successfully')
    }

    emit('success')
  } catch (error: any) {
    if (error.status === 422) {
      errors.value = error.data.errors
      toast.error('Please check the form for errors')
    } else {
      toast.error(props.isEdit ? 'Failed to update event' : 'Failed to create event')
    }
  } finally {
    loading.value = false
  }
}
</script> 