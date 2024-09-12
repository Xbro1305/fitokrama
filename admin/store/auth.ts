import { defineStore } from 'pinia'

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

  const isAuthenticated = computed(() => {
    const date = new Date()
    const today = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`

    return loginDate.value === today
  })

  const login = async ({ mail, pass }: UserPayloadInterface) => {
    const { data } = await useFetch(`${backendUrl}?method=login`, {
      method: 'post',
      headers: { 'Content-Type': 'application/json' },
      body: {
        email: mail,
        password: pass,
      },
    })

    if (data.value) {
      console.log(data.value)

      const date = new Date()
      loginDate.value = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`
      email.value = mail
      password.value = pass

      localStorage.setItem('loginDate', loginDate.value)
      localStorage.setItem('email', email.value)
      localStorage.setItem('password', password.value)
    }
  }

  return { isAuthenticated, login }
})
