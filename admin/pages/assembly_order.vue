<script setup lang="ts">
import { useNotificationStore } from '~/store/notification'
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Сборка заказа' })

const { showError, showSuccess } = useNotificationStore()
const { email, password } = useAuthStore()
const config = useRuntimeConfig()
const backendUrl = config.public.backendUrl

const checkCode = async (code: string) => {
  /*
  if (code.split('/')[0] !== '002-' || !code.split('/')[1] || code.split('/')[1].length !== 6) {
    showError('Введите QR-код с листа для сборки')

    return
  }
  */

  const { data } = await useFetch(`${backendUrl}/order_details.php`, {
    method: 'POST',
    body: {
      staff_login: email,
      staff_password: password,
      // number: code.split('/')[1],
      number: '883440',
    },
  })

  if (data.value.order) {
    console.log(data.value.order)
  }
  else if (data.value.error) {
    showError(data.value.error)
  }
}
</script>

<template>
  <v-card>
    <v-card-title>
      Сборка заказа
    </v-card-title>

    <v-card-text>
      <qrcode-scan
        @code-scanned="checkCode"
      />
    </v-card-text>
  </v-card>
</template>
