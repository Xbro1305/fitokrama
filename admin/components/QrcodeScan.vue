<script setup lang="ts">
import { BrowserMultiFormatReader, Exception } from '@zxing/library'

const emit = defineEmits<{
  (e: 'codeScanned', val: string): void
}>()

const dialog = ref(false)
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

const scanner = useTemplateRef('scanner')

const start = () => {
  codeReader.decodeFromVideoDevice(undefined, scanner.value, (result) => {
    if (result) {
      emit('codeScanned', result.getText())

      dialog.value = false
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
    <template #activator="{ props: activatorProps }">
      <v-btn
        icon
        color="success"
        v-bind="activatorProps"
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
          Отмена
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
