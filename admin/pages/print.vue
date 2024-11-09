<script setup lang="ts">
import { useAuthStore } from '~/store/auth'
import { useNotificationStore } from '~/store/notification'

useHead({ title: 'Автопечать' })

const { email, password } = useAuthStore()
const { showError } = useNotificationStore()
const config = useRuntimeConfig()

const backendUrl = config.public.backendUrl

const isUseragentCorrect = ref(false)
const pausePrint = ref(false)

let intervalId
// const forPrint = ref([])

if (window.navigator.userAgent === 'adminpage configuration') {
  isUseragentCorrect.value = true

  intervalId = setInterval(() => {
    if (pausePrint.value) {
      return
    }

    const { data, error } = useFetch(`${backendUrl}/order_print_for_assembly.php`, {
      method: 'POST',
      body: {
        staff_login: email,
        staff_password: password,
      },
    })

    if (data && data.value && data.value.html_print) {
      // forPrint.value.push(data.value.html_print)
      const html = data.value.html_print

      if (html) {
        const printWindow = window.open('', '_blank')
        printWindow?.document.write(html)
        sleep(200).then(() => {
          printWindow?.document.close()
          printWindow?.focus()
          printWindow?.print()
        })
      }
    }
    else if (error) {
      showError('Ошибка соединения с сервером')
    }
  }, 1000)
}

onUnmounted(() => clearInterval(intervalId))

const sleep = (ms: number) => new Promise((r: never) => setTimeout(r, ms))

/*
const printClick = () => {
  const html = forPrint.value.pop()

  if (html) {
    const printWindow = window.open('', '_blank')
    printWindow?.document.write(html)
    sleep(200).then(() => {
      printWindow?.document.close()
      printWindow?.focus()
      printWindow?.print()
    })
  }
}
*/
</script>

<template>
  <v-card>
    <v-card-title>
      Автопечать заказов на сборку
    </v-card-title>

    <v-card-text>
      <v-alert
        v-if="!isUseragentCorrect"
        color="error"
      >
        Запустите Supermium с правильной настройкой для автопечати
      </v-alert>
      <v-alert
        v-else
        color="success"
      >
        Ожидание заказов для печати

        <v-progress-circular
          color="error"
          indeterminate
        />
      </v-alert>

      <v-checkbox
        v-model="pausePrint"
        label="Приостановить печать"
      />

      <!--
      <v-btn
        v-if="forPrint.length > 0"
        color="primary"
        class="mt-2"
        @click="printClick"
      >
        Печать ({{ forPrint.length }})
      </v-btn>
      -->
    </v-card-text>
  </v-card>
</template>
