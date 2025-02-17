export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  const publicPages = ['/login', '/register']
  const authRequired = !publicPages.includes(to.path)

  if (!authStore.user && authRequired) {
    return navigateTo('/login')
  }
}) 