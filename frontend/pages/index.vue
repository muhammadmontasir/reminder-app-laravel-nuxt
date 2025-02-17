<template>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-semibold text-gray-900">Events</h1>
          <UiBaseButton 
            variant="primary" 
            @click="navigateTo('/events/create')"
          >
            Create Event
          </UiBaseButton>
        </div>
        
        <div class="mt-6">
          <EventsEventList />
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  definePageMeta({
    layout: 'default',
    middleware: ['auth']
  })
  </script>