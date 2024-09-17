<script setup lang="ts">
const config = useRuntimeConfig()

const appName = config.public.appName
</script>

<template>
  <v-app>
    <v-app-bar>
      <v-app-bar-title>{{ appName }}</v-app-bar-title>
    </v-app-bar>

    <v-main>
      <v-container
        fluid
        class="fill-height"
      >
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
