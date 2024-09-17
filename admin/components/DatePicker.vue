<script setup>
import { ref, computed, watch } from 'vue'

const { label, color, modelValue } = defineProps([
  'label',
  'color',
  'modelValue',
])
const emit = defineEmits('update:modelValue')

const isMenuOpen = ref(false)
const selectedDate = ref(modelValue)

const formattedDate = computed(() => {
  console.log(selectedDate.value)
  return selectedDate.value ? new Date(selectedDate.value).toISOString().substring(0, 10) : ''
})

watch(modelValue, (newDate) => {
  selectedDate.value = newDate
})

watch(selectedDate, (newDate) => {
  emit('update:modelValue', newDate)
})
</script>

<template>
  <v-menu
    v-model="isMenuOpen"
    :close-on-content-click="false"
  >
    <template #activator="{ props }">
      <v-text-field
        :label="label"
        :model-value="formattedDate"
        readonly
        v-bind="props"
        variant="solo"
        hide-details
      />
    </template>
    <v-date-picker
      v-model="selectedDate"
      hide-actions
      title=""
      :color="color"
    >
      <template #header />
    </v-date-picker>
  </v-menu>
</template>

<style>
.v-overlay__content:has(> .v-date-picker) {
  min-width: auto!important;
}
.v-picker-title {
  padding: 0 !important;
}
</style>
