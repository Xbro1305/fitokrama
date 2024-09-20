<script setup lang="ts">
import { useAuthStore } from '~/store/auth'

const { role } = useAuthStore()

const items = [
  {
    title: 'Автопечать',
    props: {
      to: '/print',
      prependIcon: 'mdi-printer',
    },
  },
]

if (role) {
  items.push({
    title: 'Все заказы',
    props: {
      to: '/',
      prependIcon: 'mdi-archive',
    },
  })
}

if (['main', 'store'].includes(role)) {
  items.push({
    title: 'Сборка заказа',
    props: {
      to: '/assembly_order',
      prependIcon: 'mdi-archive-arrow-down',
    },
  })

  items.push({
    title: 'Сборка на почту',
    props: {
      to: '/assembly_post',
      prependIcon: 'mdi-archive-arrow-down',
    },
  })
}

if (['main', 'postman'].includes(role)) {
  items.push({
    title: 'Отправка почты',
    props: {
      to: '/send_post',
      prependIcon: 'mdi-archive-arrow-up',
    },
  })
}

if (['main', 'buyer', 'manager'].includes(role)) {
  items.push({
    title: 'Товары',
    props: {
      to: '/products',
      prependIcon: 'mdi-archive',
    },
  })
}
</script>

<template>
  <v-list :items="items" />
</template>
