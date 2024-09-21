<script setup lang="ts">
import { useAuthStore } from '~/store/auth'
import { useNotificationStore } from '~/store/notification'

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
})

const editItem = (item) => {
  const art = item.art.split('=')[1]

  const { data } = await useFetch(`${backendUrl}/good_details.php`, {
    method: 'POST',
    body: {
      email: email,
      password: password,
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
        <template v-slot:item.actions="{ item }">
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
            disabled
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />

          <v-btn
            @click="dialog = false"
          >
            Отмена
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
