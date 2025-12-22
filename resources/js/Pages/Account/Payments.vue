<template>
  <StorefrontLayout>
    <div class="space-y-8">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Account</p>
          <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Payment methods</h1>
          <p class="text-sm text-slate-500">Manage your saved cards and wallets like Noon.</p>
        </div>
        <Link href="/account" class="btn-ghost text-sm">Back to profile</Link>
      </div>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="card space-y-6 p-6">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Saved methods</h2>
            <p class="text-sm text-slate-500">Default method is listed first.</p>
          </div>

          <div v-if="paymentMethods.length" class="space-y-3">
            <div v-for="method in paymentMethods" :key="method.id" class="rounded-xl border border-slate-100 p-4 text-sm">
              <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                  <p class="font-semibold text-slate-900">
                    {{ method.nickname || method.provider }}
                    <span v-if="method.is_default" class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Default</span>
                  </p>
                  <p class="text-slate-500">
                    {{ method.brand || 'Card' }} <span v-if="method.last4">**** {{ method.last4 }}</span>
                  </p>
                  <p v-if="method.exp_month && method.exp_year" class="text-slate-500">
                    Expires {{ method.exp_month }}/{{ method.exp_year }}
                  </p>
                </div>
                <button type="button" class="btn-ghost text-xs" @click="removePaymentMethod(method.id)">Remove</button>
              </div>
            </div>
          </div>
          <EmptyState
            v-else
            variant="compact"
            eyebrow="Payments"
            title="No payment methods yet"
            message="Save your preferred method for faster checkout."
          />
        </div>

        <div class="card space-y-4 p-6">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Add payment method</h2>
            <p class="text-sm text-slate-500">New method can be set as default.</p>
          </div>
          <form class="grid gap-3 sm:grid-cols-2" @submit.prevent="addPaymentMethod">
            <input v-model="paymentForm.provider" type="text" placeholder="Provider (e.g. card, momo)" class="input-base" />
            <input v-model="paymentForm.brand" type="text" placeholder="Brand" class="input-base" />
            <input v-model="paymentForm.last4" type="text" placeholder="Last 4 digits" class="input-base" />
            <input v-model="paymentForm.nickname" type="text" placeholder="Nickname" class="input-base" />
            <input v-model="paymentForm.exp_month" type="number" min="1" max="12" placeholder="Exp month" class="input-base" />
            <input v-model="paymentForm.exp_year" type="number" min="2024" max="2100" placeholder="Exp year" class="input-base" />
            <label class="flex items-center gap-2 text-xs text-slate-600 sm:col-span-2">
              <input v-model="paymentForm.is_default" type="checkbox" class="rounded border-slate-300" />
              Set as default
            </label>
            <div class="sm:col-span-2">
              <button type="submit" class="btn-primary w-full" :disabled="paymentForm.processing">
                {{ paymentForm.processing ? 'Saving...' : 'Save method' }}
              </button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  paymentMethods: { type: Array, default: () => [] },
})

const paymentForm = useForm({
  provider: '',
  brand: '',
  last4: '',
  exp_month: '',
  exp_year: '',
  nickname: '',
  is_default: false,
})

const addPaymentMethod = () => {
  paymentForm.post('/account/payment-methods', {
    preserveScroll: true,
    onSuccess: () => paymentForm.reset(),
  })
}

const removePaymentMethod = (id) => {
  router.delete(`/account/payment-methods/${id}`, { preserveScroll: true })
}
</script>
