<template>
    <div class="py-6">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-semibold text-gray-900">Import & Sync</h1>
          <UiBaseButton 
            variant="primary" 
            @click="navigateTo('/')" 
            class="bg-gray-50 text-gray-600"
          >
            Back to Events
          </UiBaseButton>
        </div>
        
        <div class="mt-6 space-y-6">
          <!-- CSV Import -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium text-gray-900">Import Events</h2>
            <p class="mt-1 text-sm text-gray-500">Upload a CSV file to import events</p>
            
            <div class="mt-4">
              <input
                type="file"
                ref="fileInput"
                accept=".csv"
                class="hidden"
                @change="handleFileUpload"
              >
              <UiBaseButton
                variant="primary"
                :disabled="importing"
                @click="$refs.fileInput.click()"
              >
                {{ importing ? 'Importing...' : 'Select CSV File' }}
              </UiBaseButton>
              <p v-if="selectedFile" class="mt-2 text-sm text-gray-500">
                Selected file: {{ selectedFile.name }}
              </p>
            </div>
          </div>
  
          <!-- Sync Events -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium text-gray-900">Sync Events</h2>
            <p class="mt-1 text-sm text-gray-500">
              Sync {{ pendingEventsCount }} pending events with the server
            </p>
            
            <div class="mt-4">
              <UiBaseButton
                variant="primary"
                :disabled="syncing || pendingEventsCount === 0"
                @click="handleSync"
              >
                {{ syncing ? 'Syncing...' : 'Sync Events' }}
              </UiBaseButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  definePageMeta({
    layout: 'default',
    middleware: ['auth']
  })
  
  const fileInput = ref<HTMLInputElement | null>(null)
  const importing = ref(false)
  const syncing = ref(false)
  const pendingEventsCount = ref(0)
  const selectedFile = ref<File | null>(null)
  const eventStore = useEventStore()
  const toast = useToast()
  
  const handleFileUpload = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return
  
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
      toast.error('Please provide a CSV file')
      return
    }

    selectedFile.value = file

    try {
      importing.value = true
      const formData = new FormData()
      formData.append('file', file)

      const api = useApi()
      await api.post('/events/import', formData, true)
      toast.success('Events imported successfully')
      
      if (fileInput.value) {
        fileInput.value.value = ''
        selectedFile.value = null
      }
    } catch (error) {
      toast.error('Failed to import events')
    } finally {
      importing.value = false
    }
  }
  
  const handleSync = async () => {
    try {
      syncing.value = true
      await eventStore.syncEvents()
      toast.success('Events synced successfully')
      pendingEventsCount.value = 0
    } catch (error) {
      toast.error('Failed to sync events')
    } finally {
      syncing.value = false
    }
  }
  
  onMounted(async () => {
    const db = useDb()
    const events = await db.getPendingEvents()
    pendingEventsCount.value = events.length
  })
  </script>