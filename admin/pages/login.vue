<script setup lang="ts">
import { useAuthStore } from '~/store/auth'

useHead({ title: 'Вход' })
definePageMeta({ layout: 'auth' })

const { isAuthenticated, login } = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: [],
  password: [],
})

const loading = ref(false)

const handleSubmit = async () => {
  await login({ mail: form.email, pass: form.password })
}
</script>

<template>
  <v-card
    max-width="600px"
    class="ma-auto"
  >
    <v-card-title>
      Вход
    </v-card-title>

    <v-form @submit.prevent="handleSubmit">
      <v-card-text>
        <v-text-field
          v-model="form.email"
          label="E-mail"
          type="email"
          :error-messages="errors.email"
          @input="errors.email = []"
        />

        <v-text-field
          v-model="form.password"
          label="Пароль"
          type="password"
          :error-messages="errors.password"
          @input="errors.password = []"
        />
      </v-card-text>

      <v-card-actions>
        <v-spacer />

        <v-btn
          type="submit"
          color="primary"
          variant="flat"
          :disabled="form.email.length < 3 || !form.password || loading"
          :loading="loading"
        >
          Войти
        </v-btn>
      </v-card-actions>
    </v-form>
  </v-card>
</template>
