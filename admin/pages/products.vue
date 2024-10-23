<script setup lang="ts">
import { vMaska } from 'maska/vue'
import type { MaskInputOptions } from 'maska'
import { useAuthStore } from '~/store/auth'
import { useNotificationStore } from '~/store/notification'

const options = reactive<MaskInputOptions>({
  mask: '0.99',
  eager: true,
  tokens: {
    0: { pattern: /\d/, multiple: true },
    9: { pattern: /\d/, optional: true },
  },
})

useHead({ title: 'Товары' })

const { email, password } = useAuthStore()
const { showError, showSuccess } = useNotificationStore()
const config = useRuntimeConfig()

const backendUrl = config.public.backendUrl

const headers = [
  { title: 'Название', key: 'name' },
  { title: 'Артикул', key: 'art' },
  { title: '', key: 'actions' },
]

const products = ref([])
const product = ref(null)
const search = ref('')
const dialog = ref(false)

watch(search, async () => {
  const { data, error } = await useFetch(`${backendUrl}/search.php`, {
    method: 'POST',
    body: {
      email: email,
      password: password,
    },
    query: {
      search: search.value,
    },
  })

  if (data && data.value) {
    products.value = data.value
  }
  else if (error) {
    const { showError } = useNotificationStore()

    showError('Ошибка соединения с сервером')
  }
})

const editItem = async (item) => {
  const art = item.art.split('=')[1]

  const { data, error } = await useFetch(`${backendUrl}/good_details.php`, {
    method: 'POST',
    body: {
      staff_login: email,
      staff_password: password,
      art: art,
    },
  })

  if (data.value.good) {
    product.value = data.value.good
    dialog.value = true
  }
  else if (data.value.error) {
    showError(data.value.error)
  }
  else if (error) {
    showError('Ошибка соединения с сервером')
  }
}

const saveProduct = async () => {
  const { data, error } = await useFetch(`${backendUrl}/good_update.php`, {
    method: 'POST',
    body: {
      staff_login: email,
      staff_password: password,
      product: product.value,
    },
  })

  if (data.value.message) {
    showSuccess(data.value.message)
    dialog.value = false

    await refresh()
  }
  else if (data.value.error) {
    showError(data.value.error)
  }
  else if (error) {
    showError('Ошибка соединения с сервером')
  }
}

const refresh = async () => {
  const { data } = await useFetch(`${backendUrl}/search.php`, {
    method: 'POST',
    body: {
      email: email,
      password: password,
    },
    query: {
      search: search.value,
    },
  })

  products.value = data.value
}
</script>

<template>
  <v-card>
    <v-card-title>
      Товары
    </v-card-title>

    <v-card-text>
      <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <v-text-field
            v-model="search"
            label="Наименование, артикул"
          />
        </v-col>
      </v-row>

      <v-data-table
        :headers="headers"
        :items="products"
      >
        <template #[`item.actions`]="{ item }">
          <v-btn
            color="warning"
            icon="mdi-pencil"
            density="compact"
            @click="editItem(item)"
          />
        </template>
      </v-data-table>
    </v-card-text>

    <v-dialog
      v-model="dialog"
      max-width="600"
    >
      <v-card>
        <v-card-text>
          <v-text-field
            v-model="product.art"
            label="Артикул"
            disabled
          />

          <v-text-field
            v-model="product.name"
            label="Название"
            density="compact"
          />

          <v-textarea
            v-model="product.description_short"
            label="Короткое описание"
            density="compact"
          />

          <v-textarea
            v-model="product.description_full"
            label="Полное описание"
            density="compact"
          />

          <v-text-field
            v-model="product.price"
            v-maska="options"
            label="Цена"
            density="compact"
          />

          <v-text-field
            v-model="product.price_old"
            v-maska="options"
            label="Цена старая"
            density="compact"
          />

          <v-text-field
            v-model="product.qty"
            v-maska="'#############'"
            label="Количество"
            density="compact"
          />

          <v-text-field
            v-model="product.barcode"
            v-maska="'#############'"
            label="Barcode"
            density="compact"
          />

          <v-text-field
            v-model="product.producer"
            label="Производитель"
            density="compact"
          />

          <v-text-field
            v-model="product.producer_country"
            label="Страна"
            density="compact"
          />

          <v-text-field
            v-model="product.cat"
            label="Категория"
            density="compact"
          />

          <v-text-field
            v-model="product.subcat"
            label="Подкатегория"
            density="compact"
          />

          <v-text-field
            v-model="product.koef_ed_izm"
            v-maska="options"
            label="Коэффициент ед. изм."
            density="compact"
          />

          <v-text-field
            v-model="product.ed_izm_name"
            label="Название ед. изм."
            density="compact"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />

          <v-btn
            @click="dialog = false"
          >
            Отмена
          </v-btn>

          <v-btn
            @click="saveProduct"
          >
            Сохранить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
