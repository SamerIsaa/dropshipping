<template>
  <StorefrontLayout>
    <div class="space-y-8">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Account</p>
          <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Wishlist</h1>
          <p class="text-sm text-slate-500">Save CJ favorites and revisit them later.</p>
        </div>
        <Link href="/products" class="btn-ghost text-sm">Continue shopping</Link>
      </div>

      <div v-if="products.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="product in products" :key="product.id" class="relative">
          <ProductCard :product="product" :currency="currency" />
          <div class="absolute right-3 top-3">
            <Link
              :href="route('account.wishlist.destroy', product.id)"
              method="delete"
              as="button"
              class="rounded-full border border-slate-200 bg-white/80 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-slate-700 transition hover:border-slate-300 hover:bg-white"
            >
              Remove
            </Link>
          </div>
        </div>
      </div>

      <EmptyState
        v-else
        variant="compact"
        eyebrow="Wishlist"
        title="Nothing saved yet"
        message="Tap the heart icon on a product to save it for later."
      >
        <template #actions>
          <Link href="/products" class="btn-primary text-xs">Browse products</Link>
        </template>
      </EmptyState>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  products: { type: Array, default: () => [] },
  currency: { type: String, default: 'USD' },
})

</script>
