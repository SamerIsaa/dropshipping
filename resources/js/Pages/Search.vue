<template>
  <StorefrontLayout>
    <section class="space-y-6">
  <div class="space-y-2">
    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ t('Search') }}</p>
    <h1 class="text-3xl font-semibold tracking-tight text-slate-900">
      {{ t('Results for ":query"', { query: query || t('All products') }) }}
    </h1>
    <p class="text-sm text-slate-600">
      {{ t(':count items found', { count: resultsPager.total ?? 0 }) }}
    </p>
  </div>

  <div v-if="results.length" class="space-y-4">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <ProductCard
        v-for="product in results"
        :key="product.id"
        :product="product"
        :currency="currency"
      />
    </div>
    <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="btn-ghost px-3 py-2 text-xs"
          :disabled="resultsPager.current_page <= 1"
          @click="goToPage((resultsPager.current_page ?? 1) - 1)"
        >
          {{ t('Previous slide') }}
        </button>
        <button
          type="button"
          class="btn-ghost px-3 py-2 text-xs"
          :disabled="! hasMore"
          @click="goToPage((resultsPager.current_page ?? 1) + 1)"
        >
          {{ t('Next slide') }}
        </button>
      </div>
      <span>
        {{ t('Page') }} {{ resultsPager.current_page ?? 1 }} / {{ resultsPager.last_page ?? 1 }}
      </span>
    </div>
  </div>
      <EmptyState
        v-else
        :eyebrow="t('Search')"
        :title="t('Nothing matched that search')"
        :message="t('Try a different keyword or browse curated collections instead.')"
      >
        <template #actions>
          <Link href="/products" class="btn-primary">{{ t('Browse catalog') }}</Link>
          <Link href="/support" class="btn-ghost">{{ t('Ask for help') }}</Link>
        </template>
      </EmptyState>
    </section>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useTranslations } from '@/i18n'

const { t } = useTranslations()

const props = defineProps({
  results: { type: Object, default: () => ({ data: [] }) },
  query: { type: String, default: '' },
  currency: { type: String, default: 'USD' },
})

const query = computed(() => props.query ?? '')
const resultsPager = computed(() => props.results ?? { data: [] })
const results = computed(() => resultsPager.value.data ?? [])
const hasMore = computed(() => (resultsPager.value.current_page ?? 1) < (resultsPager.value.last_page ?? 1))

const goToPage = (page) => {
  if (page < 1 || page > (resultsPager.value.last_page ?? 1)) {
    return
  }
  router.get('/search', { q: props.query, page }, { preserveState: true })
}
</script>
