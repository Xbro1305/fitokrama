<script setup lang="ts">
import { ref } from 'vue'
import SidebarMenu from '~/components/SidebarMenu.vue'

const drawer = ref<boolean | null>(null)

const config = useRuntimeConfig()

const appName = config.public.appName
</script>

<template>
  <v-app>
    <v-app-bar>
      <v-app-bar-nav-icon @click="drawer = !drawer" />

      <v-app-bar-title>{{ appName }}</v-app-bar-title>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer">
      <sidebar-menu />
    </v-navigation-drawer>

    <v-main>
      <v-container fluid>
        <v-row>
          <v-col cols="12">
            <slot />
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <notification-component />
  </v-app>
</template>
