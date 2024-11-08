<script setup lang="ts">
import { vMaska } from 'maska/vue'
import { useNotificationStore } from '~/store/notification'
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Сборка заказа' })

const { showError, showSuccess } = useNotificationStore()
const { email, password } = useAuthStore()
const config = useRuntimeConfig()
const backendUrl = config.public.backendUrl
const order = ref(null)
const barcode = ref('')
const qty = ref('1')
const code2 = ref('')
const barcodeErrors = ref([])
const qtyErrors = ref([])
const dialog = ref(false)
const confirmationDialog = ref(false)

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
  // { title: 'Barcode', key: 'barcode' },
  { title: 'Название', key: 'name' },
  { title: 'Количество', key: 'qty' },
  { title: 'Собрано', key: 'qty_as' },
]

const checkCode = async (code: string) => {
  if (code.split('/')[0] !== '002-' || !code.split('/')[1] || code.split('/')[1].length !== 6) {
    showError('Введите QR-код с листа для сборки')

    return
  }

  const { data, error } = await useFetch(`${backendUrl}/order_details.php`, {
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
    dialog.value = true
    code2.value = ''

    order.value.goods.forEach((good) => {
      if (Number.parseInt(good.qty_as) !== 0) {
        showError('Заказ содержит уже отсканированные товары, однако их необходимо отсканировать снова')
      }
    })
  }
  else if (data.value.error) {
    showError(data.value.error)
  }
  else if (error) {
    showError('Ошибка соединения с сервером')
  }
}

const sleep = (ms: number) => new Promise((r: never) => setTimeout(r, ms))

const addItem = async () => {
  let barcodeExists = false

  if (!order.value) {
    return
  }

  order.value.goods.forEach((good) => {
    if (good.barcode === barcode.value) {
      barcodeExists = true
    }
  })

  if (!barcodeExists) {
    barcodeErrors.value = ['Лишний товар!']
    const audio = new Audio('/sounds/short_error.mp3')
    await audio.play()
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
        const audio = new Audio('/sounds/short_error.mp3')
        audio.play()
      }
      else {
        good.qty_as = Number.parseInt(good.qty_as) + Number.parseInt(qty.value)
        const audio = new Audio('/sounds/short_ok.mp3')
        audio.play()
      }
    }
  })

  let completed = true

  order.value.goods.forEach((good) => {
    if (Number.parseInt(good.qty_as) < Number.parseInt(good.qty)) {
      completed = false
    }
  })

  barcode.value = ''

  if (completed) {
    showSuccess('Заказ собран!')
    // good.qty_as = Number.parseInt(good.qty_as) + Number.parseInt(qty.value)
    const audio = new Audio('/sounds/long_ok.mp3')
    audio.play()

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
      closeForm()
    }

    if (data.value.error) {
      showError(data.value.error)
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

const plusQty = () => {
  if (Number.parseInt(qty.value) < 999) {
    qty.value = Number.parseInt(qty.value) + 1
  }
}

const minusQty = () => {
  if (Number.parseInt(qty.value) > 1) {
    qty.value = Number.parseInt(qty.value) - 1
  }
}

//
const codeUpdated = () => {
  if (code2.value.split('/')[0] !== '002-' || !code2.value.split('/')[1] || code2.value.split('/')[1].length !== 6) {
    return
  }

  checkCode(code2.value)
}

const closeForm = () => {
  confirmationDialog.value = false
  dialog.value = false
}

const closeFormConfirmation = () => {
  confirmationDialog.value = true
}

const barcodeScanned = (result: string) => {
  barcode.value = result
  addItem()
}
</script>

<template>
  <v-card>
    <v-card-title>
      Сборка заказа
    </v-card-title>

    <v-card-text>
      <!-- v-maska="'002-/######'" -->
      <v-text-field
        v-model="code2"
        label="Код"
        @input="codeUpdated"
      >
        <template #prepend>
          <qrcode-scan
            @code-scanned="checkCode"
          />
        </template>
      </v-text-field>

      <v-dialog
        v-model="dialog"
        transition="dialog-bottom-transition"
        fullscreen
      >
        <v-card>
          <v-toolbar>
            <v-btn
              icon="mdi-close"
              @click="closeFormConfirmation"
            />

            <v-toolbar-title>
              №{{ order.number }}
            </v-toolbar-title>
          </v-toolbar>

          <v-card-text v-if="order">
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
                  @keydown.enter.prevent="addItem"
                >
                  <template #prepend>
                    <qrcode-scan
                      @code-scanned="barcodeScanned"
                    />
                  </template>
                </v-text-field>
              </v-col>

              <v-col
                cols="12"
                md="5"
              >
                <v-text-field
                  v-model="qty"
                  v-maska="'###'"
                  label="Количество"
                  density="compact"
                  variant="outlined"
                  append-icon="mdi-plus"
                  prepend-icon="mdi-minus"
                  :max-width="180"
                  :error-messages="qtyErrors"
                  class="centered-input"
                  @input="qtyErrors = []"
                  @click:prepend="minusQty"
                  @click:append="plusQty"
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
      </v-dialog>

      <v-dialog
        v-model="confirmationDialog"
        max-width="700"
      >
        <v-card title="Заказ не собран и не будет сохранен. Вы уверены?">
          <v-card-actions>
            <v-spacer />

            <v-btn
              text="Закрыть"
              variant="text"
              @click="closeForm"
            />
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card-text>
  </v-card>
</template>

<style>
.centered-input input {
  text-align: center
}
</style>
