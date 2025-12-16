<template>
  <StorefrontLayout>
    <div class="grid gap-10 lg:grid-cols-[1.2fr,0.8fr]">
      <div class="space-y-4">
        <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-slate-50">
          <img
            v-if="product.media?.[0]"
            :src="product.media[0]"
            :alt="product.name"
            class="h-full w-full object-cover"
          />
        </div>
        <div class="grid grid-cols-4 gap-3">
          <div
            v-for="(image, idx) in product.media"
            :key="idx"
            class="aspect-square overflow-hidden rounded-xl bg-slate-50"
          >
            <img :src="image" :alt="product.name" class="h-full w-full object-cover" />
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="space-y-2">
          <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Dispatch Studio</p>
          <h1 class="text-3xl font-semibold tracking-tight text-slate-900">{{ product.name }}</h1>
          <p class="text-sm text-slate-600">{{ product.description }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-2xl font-semibold text-slate-900">{{ currency }} {{ product.price }}</span>
          <span class="text-xs text-slate-400">Ships in {{ product.lead_time_days ?? 7 }} days</span>
        </div>

        <div class="space-y-3">
          <label class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Options</label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in product.options ?? []"
              :key="option"
              type="button"
              class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-800 hover:border-slate-300"
            >
              {{ option }}
            </button>
          </div>
        </div>

        <form @submit.prevent="$emit('add-to-cart', product)">
          <div class="flex items-center gap-3">
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900"
            >
              Add to cart
            </button>
            <p class="text-xs text-slate-500">
              Customs & duties shown at checkout. Delivery to Côte d’Ivoire with tracking.
            </p>
          </div>
        </form>

        <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
          <p class="text-sm font-semibold text-slate-900">Delivery & Customs</p>
          <ul class="space-y-1 text-sm text-slate-600">
            <li>• Tracked shipping with local updates.</li>
            <li>• Duties and VAT disclosed before payment.</li>
            <li>• Returns supported for damaged or incorrect items.</li>
          </ul>
        </div>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'

defineProps({
  product: { type: Object, required: true },
  currency: { type: String, default: 'USD' },
})

defineEmits(['add-to-cart'])
</script>
