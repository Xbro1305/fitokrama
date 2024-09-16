<script setup lang="ts">
import { useNotificationStore } from '~/store/notification'

const notificationStore = useNotificationStore()

const color = ref<string>('')
const text = ref<string>('')
const active = ref<boolean>(false)

notificationStore.$onAction(({ after }) => {
  after(() => {
    if (!notificationStore.text) {
      return
    }

    color.value = notificationStore.color
    text.value = notificationStore.text
    active.value = true
  })
})
</script>

<template>
  <v-snackbar
    v-model="active"
    :color="color"
  >
    {{ text }}

    <template #actions>
      <v-btn
        variant="text"
        @click="active = false"
      >
        Закрыть
      </v-btn>
    </template>
  </v-snackbar>
</template>
