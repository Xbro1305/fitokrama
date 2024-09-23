import { defineStore } from 'pinia'
import { useNotificationStore } from '~/store/notification'

interface UserPayloadInterface {
  mail: string
  pass: string
}

export const useAuthStore = defineStore('auth', () => {
  const config = useRuntimeConfig()

  const backendUrl = config.public.backendUrl

  const loginDate = ref(localStorage.getItem('loginDate'))
  const email = ref(localStorage.getItem('email'))
  const password = ref(localStorage.getItem('password'))
  const role = ref(localStorage.getItem('role'))

  const isAuthenticated = computed(() => {
    const date = new Date()
    const today = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`

    return loginDate.value === today
  })

  const login = async ({ mail, pass }: UserPayloadInterface) => {
    const { data, error } = await useFetch(`${backendUrl}/admin/login.php`, {
      method: 'post',
      headers: { 'Content-Type': 'application/json' },
      body: {
        email: mail,
        password: pass,
      },
    })

    if (data.value) {
      if (data.value.error) {
        console.log(data.value.error)
      }
      else if (data.value.role) {
        const date = new Date()
        loginDate.value = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`
        email.value = mail
        password.value = pass
        role.value = data.value.role

        localStorage.setItem('loginDate', loginDate.value)
        localStorage.setItem('email', email.value)
        localStorage.setItem('password', password.value)
        localStorage.setItem('role', data.value.role)

        navigateTo('/print')
      }
    }
    else if (error) {
      const { showError } = useNotificationStore()

      showError('Ошибка соединения с сервером')
    }
  }

  return { isAuthenticated, login, role, email, password }
})
