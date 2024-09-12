<script setup lang="ts">
useHead({ title: 'Все заказы' })

const headers = [
  { title: 'ID' },
  { title: 'Дата' },
  { title: 'Статус' },
]
</script>

<template>
  <v-card>
    <v-card-title>
      Все заказы
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="headers"
      />
    </v-card-text>
  </v-card>
</template>
