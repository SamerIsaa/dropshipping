import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const applyReplacements = (text, replacements) => {
  let resolved = String(text ?? '')
  if (!replacements || typeof replacements !== 'object') {
    return resolved
  }

  Object.entries(replacements).forEach(([key, value]) => {
    const safeValue = String(value ?? '')
    resolved = resolved
      .replace(new RegExp(`:${key}\\b`, 'g'), safeValue)
      .replace(new RegExp(`\\{\\{\\s*${key}\\s*\\}\\}`, 'g'), safeValue)
  })

  return resolved
}

export const useTranslations = () => {
  const page = usePage()

  const translations = computed(() => page.props.translations ?? {})
  const locale = computed(() => page.props.locale ?? 'en')
  const availableLocales = computed(() => page.props.availableLocales ?? { en: 'English', fr: 'Français' })

  const t = (key, replacements = {}) => {
    if (!key) {
      return ''
    }
    const text = translations.value[key] ?? key
    return applyReplacements(text, replacements)
  }

  return {
    t,
    locale,
    availableLocales,
  }
}
