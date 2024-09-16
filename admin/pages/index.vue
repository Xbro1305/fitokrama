<script setup lang="ts">
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Все заказы' })

const { email, password } = useAuthStore()

const config = useRuntimeConfig()

const backendUrl = config.public.backendUrl

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Номер', key: 'number' },
  { title: 'Дата', key: 'datetime_create' },
  { title: 'Сумма', key: 'sum' },
]

const { data } = await useFetch(`${backendUrl}/admin/orders.php`, {
  method: 'POST',
  body: {
    email: email,
    password: password,
    date_from: '2000-01-01',
    date_to: '2025-01-01',
  },
})

const date = new Date().toISOString().substring(0, 10)

const startDate = ref(date)
const endDate = ref(date)

const orders = reactive([])

if (data.value && data.value.orders) {
  orders.push(...data.value.orders)
}
</script>

<template>
  <v-card>
    <v-card-title>
      Все заказы
    </v-card-title>

    <v-card-text>
      <!--
      <date-picker
        v-model="startDate"
        label="Start Date"
        color="primary"
      />
      -->

      <v-data-table
        :headers="headers"
        :items="orders"
      />
    </v-card-text>
  </v-card>
</template>
