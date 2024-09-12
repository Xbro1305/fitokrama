<script setup lang="ts">
const items = [
  {
    title: 'Автопечать',
    props: {
      to: '/print',
      prependIcon: 'mdi-printer',
    },
  },
  {
    title: 'Все заказы',
    props: {
      to: '/',
      prependIcon: 'mdi-archive',
    },
  },
  {
    title: 'Сборка заказа',
    props: {
      to: '/assembly_order',
      prependIcon: 'mdi-archive-arrow-down',
    },
  },
  {
    title: 'Сборка на почту',
    props: {
      to: '/assembly_post',
      prependIcon: 'mdi-archive-arrow-down',
    },
  },
  {
    title: 'Отправка почты',
    props: {
      to: '/send_post',
      prependIcon: 'mdi-archive-arrow-up',
    },
  },
  {
    title: 'Товары',
    props: {
      to: '/products',
      prependIcon: 'mdi-archive',
    },
  },
]
</script>

<template>
  <v-list :items="items" />
</template>
