<script setup lang="ts">
useHead({ title: 'Сборка заказа' })

const test = (code: string) => {
  console.log('test')
  console.log(code)
}
</script>

<template>
  <v-card>
    <v-card-title>
      Сборка заказа
    </v-card-title>

    <v-card-text>
      <qrcode-scan
        @code-scanned="test"
      />
    </v-card-text>
  </v-card>
</template>
