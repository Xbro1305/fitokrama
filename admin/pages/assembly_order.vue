<script setup lang="ts">
import { useNotificationStore } from '~/store/notification'
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Сборка заказа' })

const { showError, showSuccess } = useNotificationStore()
const { email, password } = useAuthStore()
const config = useRuntimeConfig()
const backendUrl = config.public.backendUrl
const order = ref(null)
const barcode = ref('')
const qty = ref(1)
const barcodeErrors = ref([])
const qtyErrors = ref([])

watch(barcodeErrors, () => {
  setTimeout(() => {
    if (barcodeErrors.value.length > 0) {
      barcodeErrors.value = []
    }
  }, 1000)
})

watch(qtyErrors, () => {
  setTimeout(() => {
    if (qtyErrors.value.length > 0) {
      qtyErrors.value = []
    }
  }, 1000)
})

const headers = [
  { title: 'Артикул', key: 'good_art' },
  { title: 'Barcode', key: 'barcode' },
  { title: 'Название', key: 'name' },
  { title: 'Количество', key: 'qty' },
  { title: 'Собрано', key: 'qty_as' },
]

const checkCode = async (code: string) => {
  if (code.split('/')[0] !== '002-' || !code.split('/')[1] || code.split('/')[1].length !== 6) {
    showError('Введите QR-код с листа для сборки')

    return
  }

  const { data } = await useFetch(`${backendUrl}/order_details.php`, {
    method: 'POST',
    body: {
      staff_login: email,
      staff_password: password,
      number: code.split('/')[1],
      // number: '883440',
    },
  })

  if (data.value.order) {
    order.value = data.value.order

    order.value.goods.forEach((good) => {
      if (Number.parseInt(good.qty_as) !== 0) {
        showError('Заказ содержит уже отсканированные товары, однако их необходимо отсканировать снова')
      }
    })
  }
  else if (data.value.error) {
    showError(data.value.error)
  }
}

const sleep = (ms: number) => new Promise((r: never) => setTimeout(r, ms))

const addItem = async () => {
  let barcodeExists = false

  order.value.goods.forEach((good) => {
    if (good.barcode === barcode.value) {
      barcodeExists = true
    }
  })

  if (!barcodeExists) {
    barcodeErrors.value = ['Лишний товар!']
    return
  }

  if (Number.parseInt(qty.value) < 1) {
    qtyErrors.value = ['Количество меньше нуля!']
    return
  }

  order.value.goods.forEach((good) => {
    if (good.barcode === barcode.value) {
      if (Number.parseInt(good.qty_as) + Number.parseInt(qty.value) > Number.parseInt(good.qty)) {
        qtyErrors.value = ['Лишний товар!']
      }
      else {
        good.qty_as = Number.parseInt(good.qty_as) + Number.parseInt(qty.value)
      }
    }
  })

  let completed = true

  order.value.goods.forEach((good) => {
    if (Number.parseInt(good.qty_as) < Number.parseInt(good.qty)) {
      completed = false
    }
  })

  if (completed) {
    showSuccess('Заказ собран!')

    const goods = []

    order.value.goods.forEach((good) => {
      goods.push({
        good_art: good.good_art,
        qty_as: good.qty_as,
      })
    })

    const { data } = await useFetch(`${backendUrl}/order_assembled.php`, {
      method: 'POST',
      body: {
        staff_login: email,
        staff_password: password,
        order_number: order.value.number,
        goods: goods,
      },
    })

    if (data.value.message) {
      showSuccess(data.value.message)
    }

    if (data.value.error) {
      showSuccess(data.value.error)
    }

    if (data.value.html_for_print) {
      const printWindow = window.open('', '_blank')
      printWindow?.document.write(data.value.html_for_print)
      sleep(200).then(() => {
        printWindow?.document.close()
        printWindow?.focus()
        printWindow?.print()
      })
    }
  }
}

const colorRowItem = (item) => {
  if (Number.parseInt(item.item.qty) === Number.parseInt(item.item.qty_as)) {
    return { class: 'bg-green' }
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

    <v-card-title
      v-if="order"
    >
      №{{ order.number }}
    </v-card-title>

    <v-card-text
      v-if="order"
    >
      <v-row>
        <v-col
          cols="12"
          md="5"
        >
          <v-text-field
            v-model="barcode"
            label="Barcode"
            density="compact"
            variant="outlined"
            :error-messages="barcodeErrors"
            @input="barcodeErrors = []"
          />
        </v-col>

        <v-col
          cols="12"
          md="5"
        >
          <v-text-field
            v-model="qty"
            label="Количество"
            density="compact"
            variant="outlined"
            append-icon="mdi-plus"
            prepend-icon="mdi-minus"
            :error-messages="qtyErrors"
            @input="qtyErrors = []"
            @click:prepend="qty--"
            @click:append="qty++"
          />
        </v-col>

        <v-col
          cols="12"
          md="2"
        >
          <v-btn
            color="primary"
            :disabled="!barcode"
            @click="addItem"
          >
            Добавить
          </v-btn>
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :items="order.goods"
        :row-props="colorRowItem"
      />
    </v-card-text>
  </v-card>
</template>
