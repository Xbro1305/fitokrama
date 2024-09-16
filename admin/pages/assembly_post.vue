<script setup lang="ts">
import { useAuthStore } from '~/store/auth'
import { useNotificationStore } from '~/store/notification'

useHead({ title: 'Сборка на почту' })

const { email, password } = useAuthStore()
const { showError, showSuccess } = useNotificationStore()

const config = useRuntimeConfig()

const backendUrl = config.public.backendUrl

const products = ref([])

const selected = ref([])

const { data } = await useFetch(`${backendUrl}/orders_to_send.php`, {
  method: 'POST',
  body: {
    staff_login: email,
    staff_password: password,
  },
})

products.value = data.value

const headers = [
  { title: 'Название', key: 'name' },
  { title: 'Заказов', key: 'orders_count', value: item => item.orders.length },
  { title: 'Заказы', key: 'orders', value: item => item.orders.map(order => order.number).join(', ') },
]

const sleep = (ms: number) => new Promise((r: never) => setTimeout(r, ms))

const printList = async () => {
  const { data } = await useFetch(`${backendUrl}/orders_list_to_send.php`, {
    method: 'POST',
    body: {
      staff_login: email,
      staff_password: password,
      methods: selected.value,
    },
  })

  if (data.value.message) {
    showSuccess(data.value.message)
  }
  else if (data.value.error) {
    showError(data.value.error)
  }

  if (data.value.for_print) {
    const printWindow = window.open('', '_blank')
    printWindow?.document.write(data.value.for_print)

    sleep(200).then(() => {
      printWindow?.document.close()
      printWindow?.focus()
      printWindow?.print()
    })
  }
}
</script>

<template>
  <v-card>
    <v-card-title>
      Сборка на почту
    </v-card-title>

    <v-card-text>
      <v-data-table
        v-model="selected"
        :headers="headers"
        :items="products"
        show-select
      />

      <v-btn
        v-if="selected.length > 0"
        color="primary"
        @click="printList"
      >
        Распечатать список на отправку ({{ selected.length }})
      </v-btn>
    </v-card-text>
  </v-card>
</template>
