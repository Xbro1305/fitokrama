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
  { title: 'Статус', key: 'status_text_admin' },
  { title: '', key: 'actions', sortable: false },
]

const date = new Date().toISOString().substring(0, 10)
const startDate = ref(adapter.parseISO(date))
const endDate = ref(adapter.parseISO(date))

const orders = ref([])
const order = ref({})
const showOrder = ref(false)
const statuses = ref([])
const status = ref(null)

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

  data.value.orders.forEach((order1) => {
    if (!statuses.value.includes(order1.status_text_admin)) {
      statuses.value.push(order1.status_text_admin)
    }
  })
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

const showItem = (item) => {
  order.value = item
  showOrder.value = true
}

const statusFilter = (value, query, item) => {
  return query === null || item.columns.status_text_admin === query

  /*
  return value != null &&
    query != null &&
    typeof value === 'string' &&
    value.toString().toLocaleUpperCase().indexOf(query) !== -1
  */
}
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
          md="4"
        >
          <v-date-input
            v-model="startDate"
            label="Дата, от"
          />
        </v-col>

        <v-col
          cols="12"
          md="4"
        >
          <v-date-input
            v-model="endDate"
            label="Дата, до"
          />
        </v-col>

        <v-col
          cols="12"
          md="4"
        >
          <v-select
            v-model="status"
            label="Статус"
            :items="statuses"
            clearable
          />
        </v-col>
      </v-row>

      <v-data-table
        :custom-filter="statusFilter"
        :headers="headers"
        :items="orders"
        :search="status"
      >
        <template #[`item.actions`]="{ item }">
          <v-btn
            color="primary"
            icon="mdi-eye"
            density="compact"
            @click="showItem(item)"
          />
        </template>
      </v-data-table>
    </v-card-text>

    <v-dialog
      v-model="showOrder"
      max-width="600"
    >
      <v-card>
        <v-card-title>
          Детали заказа
        </v-card-title>
        <v-card-text>
          alfa_orderId: {{ order.alfa_orderId }}<br>
          alfa_url: {{ order.alfa_url }}<br>
          assembly_staff_id: {{ order.assembly_staff_id }}<br>
          client_id: {{ order.client_id }}<br>
          client_name: {{ order.client_name }}<br>
          client_phone: {{ order.client_phone }}<br>
          datetime_assembly: {{ order.datetime_assembly }}<br>
          datetime_assembly_order: {{ order.datetime_assembly_order }}<br>
          datetime_cancel: {{ order.datetime_cancel }}<br>
          datetime_create: {{ order.datetime_create }}<br>
          datetime_delivery: {{ order.datetime_delivery }}<br>
          datetime_finish: {{ order.datetime_finish }}<br>
          datetime_order_print: {{ order.datetime_order_print }}<br>
          datetime_paid: {{ order.datetime_paid }}<br>
          datetime_sent: {{ order.datetime_sent }}<br>
          datetime_wait: {{ order.datetime_wait }}<br>
          delivery_logo: {{ order.delivery_logo }}<br>
          delivery_method: {{ order.delivery_method }}<br>
          delivery_price: {{ order.delivery_price }}<br>
          delivery_submethod: {{ order.delivery_submethod }}<br>
          delivery_text: {{ order.delivery_text }}<br>
          epos_id: {{ order.epos_id }}<br>
          epos_link: {{ order.epos_link }}<br>
          hutki_billId: {{ order.hutki_billId }}<br>
          id: {{ order.id }}<br>
          internal_postcode: {{ order.internal_postcode }}<br>
          number: {{ order.number }}<br>
          order_point_address: {{ order.order_point_address }}<br>
          paid: {{ order.paid }}<br>
          parties_id: {{ order.parties_id }}<br>
          post_code: {{ order.post_code }}<br>
          status: {{ order.status }}<br>
          status_color: {{ order.status_color }}<br>
          status_text: {{ order.status_text }}<br>
          status_text_admin: {{ order.status_text_admin }}<br>
          steps: {{ order.steps }}<br>
          sum: {{ order.sum }}<br>
          track_number: {{ order.track_number }}<br>

          <v-data-table
            :headers="[{ title: 'Артикул', key: 'good_art' }, { title: 'Название', key: 'name' }, { title: 'Цена', key: 'price' }, { title: 'Количество', key: 'qty' }]"
            :items="order.goods"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />

          <v-btn @click="showOrder = false">
            Закрыть
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
