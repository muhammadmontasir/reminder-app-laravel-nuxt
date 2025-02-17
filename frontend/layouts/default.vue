<template>
    <div>
      <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16 items-center">
            <NuxtLink to="/" class="text-xl font-bold text-gray-900">
              Event Manager
            </NuxtLink>
            <nav class="flex gap-4 items-center">
              <template v-if="authStore.user">
                <NuxtLink 
                  to="/" 
                  class="text-gray-600 hover:text-gray-900"
                >
                  Events
                </NuxtLink>
                <NuxtLink 
                  to="/sync" 
                  class="text-gray-600 hover:text-gray-900"
                >
                  Import & Sync
                </NuxtLink>
                <span class="text-gray-600">{{ authStore.user.name }}</span>
                <button 
                  @click="authStore.logout"
                  class="text-gray-600 hover:text-gray-900"
                >
                  Logout
                </button>
              </template>
            </nav>
          </div>
        </div>
      </header>
  
      <main>
        <slot />
      </main>
    </div>
  </template>

<script setup lang="ts">
const authStore = useAuthStore()

// Check auth state on mount
onMounted(() => {
  authStore.fetchUser()
})
</script>