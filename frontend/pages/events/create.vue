<template>
  <div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Create Event</h1>
        <UiBaseButton 
          variant="primary" 
          @click="navigateTo('/')" 
        >
          Back to Events
        </UiBaseButton>
      </div>
      
      <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
        <EventsEventForm @success="navigateTo('/')" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: ['auth']
})
</script>