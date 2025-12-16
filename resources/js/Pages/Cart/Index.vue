<template>
  <StorefrontLayout>
    <div class="space-y-8">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Your cart</h1>
        <Link href="/products" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Continue shopping</Link>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.6fr,1fr]">
        <div class="space-y-3">
          <CartLineItem
            v-for="line in lines"
            :key="line.id"
            :line="line"
            :currency="currency"
            @remove="$emit('remove-line', line.id)"
          />
        </div>

        <aside class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
          <div class="flex items-center justify-between text-sm">
            <span>Subtotal</span>
            <span class="font-semibold text-slate-900">{{ currency }} {{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm text-slate-600">
            <span>Shipping</span>
            <span>Shown at checkout</span>
          </div>
          <div class="flex items-center justify-between text-sm text-slate-600">
            <span>Duties & VAT</span>
            <span>Calculated at checkout</span>
          </div>
          <button
            class="mt-4 w-full rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
            @click="$inertia.visit('/checkout')"
          >
            Proceed to checkout
          </button>
          <p class="text-xs text-slate-500">
            Delivery to Côte d’Ivoire with transparent customs. Expect tracking within 24-48h after fulfillment.
          </p>
        </aside>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import CartLineItem from '@/Components/CartLineItem.vue'

defineProps({
  lines: { type: Array, required: true },
  currency: { type: String, default: 'USD' },
  subtotal: { type: Number, default: 0 },
})

defineEmits(['remove-line'])
</script>
