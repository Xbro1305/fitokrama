<script setup lang="ts">
useHead({ title: 'Автопечать' })

const isUseragentCorrect = ref(false)

// if (window.navigator.userAgent === 'adminpage configuration') {
if (true) {
  isUseragentCorrect.value = true

  const { data: html_print } = await useFetch('https://fitokrama.by/admin/index.php?method=order_print_for_assembly')

  console.log(html_print)

  setInterval(() => {

  }, 5000)
}
</script>

<template>
  <v-card>
    <v-card-title>
      Автопечать заказов на сборку
    </v-card-title>

    <v-card-text>
      <v-alert
        v-if="!isUseragentCorrect"
        color="error"
      >
        Запустите Supermium с правильной настройкой для автопечати
      </v-alert>
      <v-alert
        v-else
        color="success"
      >
        Ожидание заказов для печати

        <v-progress-circular
          color="error"
          indeterminate
        />
      </v-alert>
    </v-card-text>
  </v-card>
</template>
