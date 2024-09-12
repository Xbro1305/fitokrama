import { useAuthStore } from '~/store/auth'

export default defineNuxtRouteMiddleware((to) => {
  const { isAuthenticated } = useAuthStore()

  if (isAuthenticated && to.name === 'login') {
    return navigateTo('/print')
  }

  if (!isAuthenticated && to.name !== 'login') {
    abortNavigation()
    return navigateTo('/login')
  }
})
