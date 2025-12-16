<template>
  <StorefrontLayout>
    <div class="mx-auto max-w-2xl space-y-6 text-center">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Order placed</p>
      <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Thank you</h1>
      <p class="text-sm text-slate-600">
        Your order <span class="font-semibold text-slate-900">#{{ order.number }}</span> is confirmed. We’ll share
        tracking once the supplier dispatches. Delivery to Côte d’Ivoire with transparent customs.
      </p>
      <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5 text-left">
        <div class="flex items-center justify-between text-sm">
          <span>Status</span>
          <span class="font-semibold text-slate-900">{{ order.status }}</span>
        </div>
        <div class="mt-3 text-sm text-slate-600">
          <p>Shipping to:</p>
          <p class="font-medium text-slate-900">{{ order.shippingAddress?.name }}</p>
          <p>{{ order.shippingAddress?.line1 }}</p>
          <p>{{ order.shippingAddress?.city }}, {{ order.shippingAddress?.country }}</p>
        </div>
      </div>
      <div class="flex justify-center gap-3">
        <Link
          :href="`/orders/track?number=${order.number}&email=${order.email}`"
          class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-900 hover:text-white"
        >
          Track order
        </Link>
        <Link href="/products" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">
          Keep shopping
        </Link>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'

defineProps({
  order: { type: Object, required: true },
})
</script>
