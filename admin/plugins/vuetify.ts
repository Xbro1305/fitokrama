import '@mdi/font/css/materialdesignicons.css'
import { VDateInput } from 'vuetify/labs/VDateInput'

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { ru } from 'vuetify/locale'

export default defineNuxtPlugin((app) => {
  const vuetify = createVuetify({
    theme: {
      defaultTheme: 'dark',
    },
    components: {
      VDateInput,
    },
    locale: {
      locale: 'ru',
      messages: { ru },
    },
  })
  app.vueApp.use(vuetify)
})
