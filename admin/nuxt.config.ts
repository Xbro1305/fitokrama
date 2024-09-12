// https://nuxt.com/docs/api/configuration/nuxt-config
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'

export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',

  devtools: {
    enabled: false,
  },

  ssr: false,

  build: {
    transpile: ['vuetify'],
  },

  runtimeConfig: {
    public: {
      backendUrl: '', // .env.NUXT_PUBLIC_BACKEND_URL
      appName: '', // .env.NUXT_PUBLIC_APP_NAME
    },
  },

  app: {
    baseURL: '/admin/',
  },

  modules: [
    (_options, nuxt) => {
      nuxt.hooks.hook('vite:extendConfig', (config) => {
        // @ts-expect-error Plugins undefined
        config.plugins.push(vuetify({ autoImport: true }))
      })
    },
    '@pinia/nuxt',
  ],

  vite: {
    vue: {
      template: {
        transformAssetUrls,
      },
    },
  },
})
