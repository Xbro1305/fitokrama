<script setup lang="ts">
import { BrowserMultiFormatReader, Exception } from '@zxing/library'

const dialog = ref(false)
const code = ref(null)
const sending = ref(false)
const codeReader = new BrowserMultiFormatReader()
const isMediaStreamAPISupported = navigator && navigator.mediaDevices && 'enumerateDevices' in navigator.mediaDevices

watch(dialog, () => {
  if (dialog.value) {
    nextTick(() => {
      start()
    })
  }
  else {
    codeReader.reset()
  }
})

onMounted(() => {
  if (!isMediaStreamAPISupported) {
    throw new Exception('Media Stream API is not supported')
  }

  codeReader.reset()
})

const sendCode = () => {
  if (sending.value || !code.value) {
    return
  }

  sending.value = true

  this.$axios.post('services_api.php/stores_qr', {
    products_json: code.value,
  }, {
    auth: {
      username: localStorage.getItem('login') ?? '',
      password: localStorage.getItem('password') ?? '',
    },
  })
    .then(({ data }) => {
      if (data.status === 'ok') {
        this.$notifier.showSuccess(data.message)
      }
      else {
        this.$notifier.showError(data.message)
      }
    })
    .catch(() => {
      this.$notifier.showError(this.$t('server_error').toString())
    })
    .finally(() => {
      dialog.value = false
      sending.value = false
      code.value = null
    })
}

const start = () => {
  codeReader.decodeFromVideoDevice(undefined, this.$refs.scanner, (result) => {
    if (result) {
      code.value = result.getText()
      sendCode()
    }
  })
}
</script>

<template>
  <v-dialog
    v-model="dialog"
    persistent
    max-width="600px"
  >
    <template #activator="{ on, attrs }">
      <v-btn
        icon
        color="success"
        v-bind="attrs"
        v-on="on"
      >
        <v-icon>
          mdi-qrcode-scan
        </v-icon>
      </v-btn>
    </template>

    <v-card>
      <v-card-text>
        <div class="scanner-container">
          <div>
            <video
              ref="scanner"
              poster="data:image/gif,AAAA"
            />
            <div class="overlay-element" />
          </div>
        </div>
      </v-card-text>

      <v-card-actions>
        <v-spacer />

        <v-btn
          color="error"
          @click="dialog = false"
        >
          {{ $t('cancel') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
video {
  max-width: 100%;
  max-height: 100%;
}

.scanner-container {
  position: relative;
}

.overlay-element {
  position: absolute;
  top: 0;
  width: 100%;
  height: 99%;
  background: rgba(30, 30, 30, 0.5);
  -webkit-clip-path: polygon(0% 0%, 0% 100%, 20% 100%, 20% 20%, 80% 20%, 80% 80%, 20% 80%, 20% 100%, 100% 100%, 100% 0%);
  clip-path: polygon(0% 0%, 0% 100%, 20% 100%, 20% 20%, 80% 20%, 80% 80%, 20% 80%, 20% 100%, 100% 100%, 100% 0%);
}
</style>
