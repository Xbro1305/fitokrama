<script setup lang="ts">
useHead({ title: 'Сборка заказа' })
</script>

<template>
  <v-card>
    <v-card-title>
      Сборка заказа
    </v-card-title>

    <v-card-text>
      <qrcode-scan />
    </v-card-text>
  </v-card>
</template>
