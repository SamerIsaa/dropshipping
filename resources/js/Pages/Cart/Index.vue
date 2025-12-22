<template>
  <StorefrontLayout>
    <div class="space-y-8">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ t('Your cart') }}</h1>
        <Link href="/products" class="btn-ghost">{{ t('Continue shopping') }}</Link>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.6fr,1fr]">
        <div v-if="lines.length" class="space-y-3">
          <CartLineItem
            v-for="line in lines"
            :key="line.id"
            :line="line"
            :currency="currency"
            @remove="removeLine(line.id)"
            @update="updateQty"
          />
        </div>
        <EmptyState
          v-else
          :eyebrow="t('Cart')"
          :title="t('Your cart is waiting')"
          :message="t('Add a few Azura finds and we will hold them here. Prices update automatically before checkout.')"
        >
          <template #actions>
            <Link href="/products" class="btn-primary">{{ t('Browse products') }}</Link>
            <Link href="/orders/track" class="btn-ghost">{{ t('Track existing order') }}</Link>
          </template>
        </EmptyState>

        <aside class="card-muted space-y-4 p-5">
          <div class="space-y-3">
            <form class="flex flex-col gap-3 sm:flex-row sm:items-center" @submit.prevent="applyCoupon">
              <input v-model="couponCode" type="text" :placeholder="t('Coupon code')" class="input-base flex-1" />
              <div class="flex gap-2">
                <button type="submit" class="btn-secondary">{{ t('Apply') }}</button>
                <button v-if="coupon" type="button" class="btn-ghost text-xs" @click="removeCoupon">{{ t('Remove') }}</button>
              </div>
            </form>
            <p v-if="coupon" class="text-xs text-slate-600">
              {{ t('Applied:') }} <span class="font-semibold text-slate-900">{{ coupon.code }}</span>
              <span v-if="discount"> ({{ currency }} {{ discount.toFixed(2) }} {{ t('off') }})</span>
            </p>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span>{{ t('Subtotal') }}</span>
            <span class="font-semibold text-slate-900">{{ currency }} {{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm text-green-700" v-if="discount > 0">
            <span>{{ t('Discount') }}</span>
            <span>- {{ currency }} {{ discount.toFixed(2) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm text-slate-600">
            <span>{{ t('Shipping') }}</span>
            <span>{{ t('Shown at checkout') }}</span>
          </div>
          <div class="flex items-center justify-between text-sm text-slate-600">
            <span>{{ t('Duties & VAT') }}</span>
            <span>{{ t('Calculated at checkout') }}</span>
          </div>
          <button
            :disabled="lines.length === 0"
            class="btn-primary mt-4 w-full"
            :class="{ 'cursor-not-allowed opacity-60': lines.length === 0 }"
            @click="$inertia.visit('/checkout')"
          >
            {{ t('Proceed to checkout') }}
          </button>
          <p class="text-xs text-slate-500">
            {{ t("Delivery to Cote d'Ivoire with transparent customs. Expect tracking within 24 to 48 hours after fulfillment.") }}
          </p>
        </aside>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import CartLineItem from '@/Components/CartLineItem.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { ref } from 'vue'
import { useTranslations } from '@/i18n'

const props = defineProps({
  lines: { type: Array, required: true },
  currency: { type: String, default: 'USD' },
  subtotal: { type: Number, default: 0 },
  discount: { type: Number, default: 0 },
  coupon: { type: Object, default: null },
})

const { t } = useTranslations()

const couponCode = ref('')

const removeLine = (id) => {
  router.delete(`/cart/${id}`, {
    preserveScroll: true,
  })
}

const updateQty = (id, quantity) => {
  router.patch(
    `/cart/${id}`,
    { quantity },
    { preserveScroll: true }
  )
}

const applyCoupon = () => {
  router.post(
    '/cart/coupon',
    { code: couponCode.value },
    { preserveScroll: true }
  )
}

const removeCoupon = () => {
  router.delete('/cart/coupon', { preserveScroll: true })
}
</script>
