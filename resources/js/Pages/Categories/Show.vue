<template>
  <StorefrontLayout>
    <Head :title="metaTitle">
      <meta name="description" head-key="description" :content="metaDescription" />
    </Head>

    <section
      class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-amber-50 via-white to-slate-50"
    >
      <div class="grid gap-6 p-8 lg:grid-cols-[1.2fr,0.8fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ t('Category') }}</p>
          <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ displayTitle }}</h1>
          <p v-if="displaySubtitle" class="mt-3 max-w-2xl text-sm text-slate-600">{{ displaySubtitle }}</p>
          <p v-else-if="metaDescription" class="mt-3 max-w-2xl text-sm text-slate-600">{{ metaDescription }}</p>
          <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-500">
            <span>{{ t(':count products', { count: productsPager.total ?? 0 }) }}</span>
            <span>{{ t('Tracked delivery') }}</span>
            <span>{{ t('Customs clarity') }}</span>
          </div>
          <Link
            v-if="category.hero_cta_label && category.hero_cta_link"
            :href="category.hero_cta_link"
            class="btn-primary mt-4 inline-flex"
          >
            {{ category.hero_cta_label }}
          </Link>
        </div>
        <div v-if="category.hero_image" class="flex items-center justify-center">
          <img :src="category.hero_image" :alt="category.name" class="h-56 w-full rounded-2xl object-cover shadow-lg" />
        </div>
      </div>
    </section>

    <section v-if="products.length" class="space-y-4">
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <ProductCard v-for="product in products" :key="product.id" :product="product" :currency="currency" />
      </div>
      <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-500">
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="btn-ghost px-3 py-2 text-xs"
            :disabled="productsPager.current_page <= 1"
            @click="goToPage((productsPager.current_page ?? 1) - 1)"
          >
            {{ t('Previous slide') }}
          </button>
          <button
            type="button"
            class="btn-ghost px-3 py-2 text-xs"
            :disabled="! hasMore"
            @click="goToPage((productsPager.current_page ?? 1) + 1)"
          >
            {{ t('Next slide') }}
          </button>
        </div>
        <span>
          {{ t('Page') }} {{ productsPager.current_page ?? 1 }} / {{ productsPager.last_page ?? 1 }}
        </span>
      </div>
    </section>
    <EmptyState
      v-else
      :eyebrow="t('Category')"
      :title="t('No products here yet')"
      :message="t('This collection is getting curated. Browse other categories or check back soon.')"
    >
      <template #actions>
        <Link href="/products" class="btn-primary">{{ t('Browse catalog') }}</Link>
        <Link href="/support" class="btn-ghost">{{ t('Request a product') }}</Link>
      </template>
    </EmptyState>
  </StorefrontLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useTranslations } from '@/i18n'

const props = defineProps({
  category: { type: Object, required: true },
  products: { type: Object, default: () => ({ data: [] }) },
  currency: { type: String, default: 'USD' },
})

const { t } = useTranslations()

const metaTitle = computed(() => props.category.meta_title || `${props.category.name} | Azura`)
const metaDescription = computed(() => props.category.meta_description || '')
const displayTitle = computed(() => props.category.hero_title || props.category.name)
const displaySubtitle = computed(() => props.category.hero_subtitle || props.category.description || '')
const productsPager = computed(() => props.products ?? { data: [] })
const products = computed(() => productsPager.value.data ?? [])
const hasMore = computed(() => (productsPager.value.current_page ?? 1) < (productsPager.value.last_page ?? 1))

const goToPage = (page) => {
  if (page < 1 || page > (productsPager.value.last_page ?? 1)) {
    return
  }
  router.get(`/categories/${props.category.slug}`, { page }, { preserveState: true })
}
</script>
