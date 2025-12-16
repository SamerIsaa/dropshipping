<template>
  <article class="group flex h-full flex-col justify-between rounded-2xl border border-slate-100 bg-white/80 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-slate-50">
      <img
        v-if="product.media?.[0]"
        :src="product.media[0]"
        :alt="product.name"
        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
        loading="lazy"
      />
    </div>
    <div class="mt-4 flex flex-1 flex-col justify-between gap-3">
      <div>
        <h3 class="text-base font-semibold leading-tight text-slate-900">
          <Link :href="`/products/${product.slug}`" class="hover:text-slate-600">
            {{ product.name }}
          </Link>
        </h3>
        <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ product.description }}</p>
      </div>
      <div class="flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-900">
          {{ currency }} {{ product.price }}
        </div>
        <Link
          :href="`/products/${product.slug}`"
          class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-800 transition hover:bg-slate-900 hover:text-white"
        >
          View
        </Link>
      </div>
    </div>
  </article>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  product: { type: Object, required: true },
  currency: { type: String, default: 'USD' },
})
</script>
