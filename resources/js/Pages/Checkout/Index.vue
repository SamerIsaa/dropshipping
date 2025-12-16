<template>
  <StorefrontLayout>
    <div class="space-y-8">
      <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Checkout</h1>

      <div class="grid gap-10 lg:grid-cols-[1.4fr,1fr]">
        <form class="space-y-6" @submit.prevent="$emit('submit', form)">
          <section class="rounded-2xl border border-slate-100 p-5">
            <h2 class="text-sm font-semibold text-slate-900">Contact</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
              <input v-model="form.email" type="email" required placeholder="Email" class="input" />
              <input v-model="form.phone" type="tel" required placeholder="Phone" class="input" />
            </div>
          </section>

          <section class="rounded-2xl border border-slate-100 p-5">
            <h2 class="text-sm font-semibold text-slate-900">Shipping address</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
              <input v-model="form.first_name" required placeholder="First name" class="input" />
              <input v-model="form.last_name" placeholder="Last name" class="input" />
              <input v-model="form.line1" required placeholder="Address line 1" class="input sm:col-span-2" />
              <input v-model="form.line2" placeholder="Address line 2" class="input sm:col-span-2" />
              <input v-model="form.city" required placeholder="City" class="input" />
              <input v-model="form.state" placeholder="State / Region" class="input" />
              <input v-model="form.postal_code" placeholder="Postal code" class="input" />
              <input v-model="form.country" required placeholder="Country" class="input" />
            </div>
            <textarea
              v-model="form.delivery_notes"
              rows="3"
              placeholder="Delivery notes (optional)"
              class="input mt-4 w-full"
            />
            <p class="mt-3 text-xs text-slate-500">
              Duties and VAT for Côte d’Ivoire are shown before payment. By placing the order you acknowledge customs may
              contact you if additional verification is required.
            </p>
          </section>

          <button
            type="submit"
            class="w-full rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
          >
            Place order
          </button>
        </form>

        <aside class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
          <div class="flex items-center justify-between text-sm">
            <span>Subtotal</span>
            <span class="font-semibold text-slate-900">{{ currency }} {{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span>Shipping</span>
            <span class="text-slate-600">Calculated</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span>Duties & VAT</span>
            <span class="text-slate-600">Calculated</span>
          </div>
          <div class="flex items-center justify-between text-base font-semibold text-slate-900">
            <span>Total</span>
            <span>{{ currency }} {{ total.toFixed(2) }}</span>
          </div>
          <p class="text-xs text-slate-500">
            Delivery estimates and customs details will be emailed after payment. Tracking updates within 24-48h post
            fulfillment.
          </p>
        </aside>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { reactive } from 'vue'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'

const props = defineProps({
  subtotal: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
  currency: { type: String, default: 'USD' },
})

const form = reactive({
  email: '',
  phone: '',
  first_name: '',
  last_name: '',
  line1: '',
  line2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'CI',
  delivery_notes: '',
})

defineEmits(['submit'])
</script>

<style scoped>
.input {
  @apply w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none;
}
</style>
