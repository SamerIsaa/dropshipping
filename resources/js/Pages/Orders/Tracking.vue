<template>
  <StorefrontLayout>
    <div class="mx-auto max-w-2xl space-y-6">
      <div class="space-y-3 text-center">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Track</p>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Order status</h1>
        <p class="text-sm text-slate-600">
          Enter your order number and email to see delivery status. Tracking updates appear once the supplier ships.
        </p>
      </div>

      <form class="space-y-4 rounded-2xl border border-slate-100 p-5" @submit.prevent="$emit('track', form)">
        <input v-model="form.number" required placeholder="Order number" class="input" />
        <input v-model="form.email" required type="email" placeholder="Email" class="input" />
        <button
          type="submit"
          class="w-full rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
        >
          Track order
        </button>
      </form>

      <div v-if="tracking" class="space-y-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
        <div class="flex items-center justify-between text-sm">
          <span>Status</span>
          <span class="font-semibold text-slate-900">{{ tracking.status }}</span>
        </div>
        <div class="space-y-2 text-sm text-slate-600">
          <p v-for="(event, idx) in tracking.events" :key="idx">
            {{ event.label }} — {{ event.date }}
          </p>
        </div>
        <p class="text-xs text-slate-500">
          Customs: if additional duties are needed, we will notify you before delivery. Standard delivery timelines apply
          after clearance.
        </p>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { reactive } from 'vue'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'

defineProps({
  tracking: { type: Object, default: null },
})

const form = reactive({
  number: '',
  email: '',
})

defineEmits(['track'])
</script>

<style scoped>
.input {
  @apply w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none;
}
</style>
