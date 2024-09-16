import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', () => {
  const color = ref<string>('')
  const text = ref<string>('')

  function showError(message: string): void {
    color.value = 'error'
    text.value = message
  }

  function showSuccess(message: string): void {
    color.value = 'success'
    text.value = message
  }

  return { color, text, showError, showSuccess }
})
