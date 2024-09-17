<script setup lang="ts">
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Товары' })

const { email, password } = useAuthStore()

const config = useRuntimeConfig()

const backendUrl = config.public.backendUrl

const headers = [
  { title: 'Название', key: 'name' },
  { title: 'Артикул', key: 'art' },
]

const products = ref([])
const search = ref('')

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
      />
    </v-card-text>
  </v-card>
</template>
