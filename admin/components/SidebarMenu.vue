<script setup lang="ts">
import { useAuthStore } from '~/store/auth'

const { role } = useAuthStore()

interface Props {
  to: string
  prependIcon: string
  disabled: boolean
}

interface Item {
  title: string
  props: Props
}

const items = [] as Array<Item>

items.push(
  {
    title: 'Автопечать',
    props: {
      to: '/print',
      prependIcon: 'mdi-printer',
      disabled: window.navigator.userAgent !== 'adminpage configuration',
    },
  },
)

if (role) {
  items.push({
    title: 'Все заказы',
    props: {
      to: '/',
      prependIcon: 'mdi-archive',
      disabled: window.navigator.userAgent === 'adminpage configuration',
    },
  })
}

if (['main', 'store'].includes(role)) {
  items.push({
    title: 'Сборка заказа',
    props: {
      to: '/assembly_order',
      prependIcon: 'mdi-archive-arrow-down',
      disabled: window.navigator.userAgent === 'adminpage configuration',
    },
  })

  items.push({
    title: 'Сборка на почту',
    props: {
      to: '/assembly_post',
      prependIcon: 'mdi-archive-arrow-down',
      disabled: window.navigator.userAgent === 'adminpage configuration',
    },
  })
}

if (['main', 'postman'].includes(role)) {
  items.push({
    title: 'Отправка почты',
    props: {
      to: '/send_post',
      prependIcon: 'mdi-archive-arrow-up',
      disabled: window.navigator.userAgent === 'adminpage configuration',
    },
  })
}

if (['main', 'buyer', 'manager'].includes(role)) {
  items.push({
    title: 'Товары',
    props: {
      to: '/products',
      prependIcon: 'mdi-archive',
      disabled: window.navigator.userAgent === 'adminpage configuration',
    },
  })
}
</script>

<template>
  <v-list :items="items" />
</template>
