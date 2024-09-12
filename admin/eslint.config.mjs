import { createConfigForNuxt } from '@nuxt/eslint-config/flat'

export default createConfigForNuxt({
  features: {
    standalone: true,
    tooling: true,
    typescript: true,
    stylistic: true,
  },
})
