<script setup lang="ts">
import { useNotificationStore } from '~/store/notification'
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Отправка почты' })
const { showError, showSuccess } = useNotificationStore()
const { email, password } = useAuthStore()
const config = useRuntimeConfig()
const backendUrl = config.public.backendUrl

const coordinates = reactive({
  latitude: null,
  longitude: null,
})

const success = (position) => {
  coordinates.latitude = position.coords.latitude
  coordinates.longitude = position.coords.longitude
}

const error = () => {
  showError('Unable to retrieve your location')
}

if (!navigator.geolocation) {
  showError('Geolocation is not supported by your browser')
}
else {
  navigator.geolocation.getCurrentPosition(success, error)
}

const sendCode = async (code: string) => {
  const { data, error } = await useFetch(`${backendUrl}/order_sent.php`, {
    method: 'POST',
    body: {
      staff_login: email,
      staff_password: password,
      qrcode: code,
      lat: coordinates.latitude,
      lng: coordinates.longitude,
    },
  })

  if (data && data.value.message) {
    showSuccess(data.value.message)
  }
  else if (data && data.value.error) {
    showError(data.value.error)
  }
  else if (error) {
    showError('Ошибка соединения с сервером')
  }
}
</script>

<template>
  <v-card>
    <v-card-title>
      Отправка почты
    </v-card-title>

    <v-card-text>
      <qrcode-scan
        v-if="coordinates.latitude"
        @code-scanned="sendCode"
      />
    </v-card-text>
  </v-card>
</template>
