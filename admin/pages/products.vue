<script setup lang="ts">
useHead({ title: 'Товары' })

const headers = [
  { title: 'ID' },
  { title: 'Название' },
]
</script>

<template>
  <v-card>
    <v-card-title>
      Товары
    </v-card-title>

    <v-card-text>
      <v-data-table
        :headers="headers"
      />
    </v-card-text>
  </v-card>
</template>
