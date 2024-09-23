<script setup lang="ts">
import { useDate } from 'vuetify'
import { useAuthStore } from '~/store/auth'
import { useNotificationStore } from '~/store/notification'

const adapter = useDate()
const { showError } = useNotificationStore()

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

const date = new Date().toISOString().substring(0, 10)
const startDate = ref(adapter.parseISO(date))
const endDate = ref(adapter.parseISO(date))

const orders = ref([])

const { data, error } = await useFetch(`${backendUrl}/admin/orders.php`, {
  method: 'POST',
  body: {
    email: email,
    password: password,
    date_from: `${adapter.toISO(startDate.value)} 00:00:00`,
    date_to: `${adapter.toISO(endDate.value)} 23:59:59`,
  },
})

if (data.value && data.value.orders) {
  orders.value = data.value.orders
}
else if (error) {
  showError('Ошибка соединения с сервером')
}

watch(startDate, async () => {
  const { data, error } = await useFetch(`${backendUrl}/admin/orders.php`, {
    method: 'POST',
    body: {
      email: email,
      password: password,
      date_from: `${adapter.toISO(startDate.value)} 00:00:00`,
      date_to: `${adapter.toISO(endDate.value)} 23:59:59`,
    },
  })

  if (data.value && data.value.orders) {
    orders.value = data.value.orders
  }
  else if (error) {
    showError('Ошибка соединения с сервером')
  }
})

watch(endDate, async () => {
  const { data, error } = await useFetch(`${backendUrl}/admin/orders.php`, {
    method: 'POST',
    body: {
      email: email,
      password: password,
      date_from: `${adapter.toISO(startDate.value)} 00:00:00`,
      date_to: `${adapter.toISO(endDate.value)} 23:59:59`,
    },
  })

  if (data.value && data.value.orders) {
    orders.value = data.value.orders
  }
  else if (error) {
    showError('Ошибка соединения с сервером')
  }
})
</script>

<template>
  <v-card>
    <v-card-title>
      Все заказы
    </v-card-title>

    <v-card-text>
      <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <v-date-input
            v-model="startDate"
            label="Дата, от"
          />
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <v-date-input
            v-model="endDate"
            label="Дата, до"
          />
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :items="orders"
      />
    </v-card-text>
  </v-card>
</template>
