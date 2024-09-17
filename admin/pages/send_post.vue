<script setup lang="ts">
import { useNotificationStore } from '~/store/notification'

useHead({ title: 'Отправка почты' })
const { showError } = useNotificationStore()

const success = (position) => {
  const latitude = position.coords.latitude
  const longitude = position.coords.longitude

  console.log(latitude)
  console.log(longitude)
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
</script>

<template>
  <v-card>
    <v-card-title>
      Отправка почты
    </v-card-title>

    <v-card-text>
      Отправка почты
    </v-card-text>
  </v-card>
</template>
