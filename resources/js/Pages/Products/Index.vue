<template>
  <StorefrontLayout>
    <section class="space-y-6">
      <div class="space-y-3">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">New in</p>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Refined essentials</h1>
          <p class="max-w-xl text-sm text-slate-500">
            Curated picks with reliable delivery to Côte d’Ivoire. Duties and customs are disclosed before payment.
          </p>
        </div>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <ProductCard
          v-for="product in products"
          :key="product.id"
          :product="product"
          :currency="currency"
        />
      </div>
    </section>
  </StorefrontLayout>
  </template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'

defineProps({
  products: { type: Array, required: true },
  currency: { type: String, default: 'USD' },
})
</script>
