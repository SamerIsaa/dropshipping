<template>
  <StorefrontLayout>
    <article class="mx-auto max-w-3xl space-y-8">
      <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Customs</p>
        <h1 class="text-3xl font-semibold text-slate-900">Customs Disclaimer</h1>
        <p class="text-sm text-slate-600">
          What to expect during customs clearance for shipments to Côte d’Ivoire.
        </p>
      </header>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Duties & VAT</h2>
        <p class="text-sm text-slate-600">
          Duties and VAT are estimated and shown at checkout when available. Final charges are determined by local
          customs and may vary. You are responsible for any official customs fees.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Documentation</h2>
        <p class="text-sm text-slate-600">
          Customs may request identification or additional documents. Please respond promptly to avoid delays.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Delays</h2>
        <p class="text-sm text-slate-600">
          Clearance times can vary. If a delay occurs, we will assist with updates and work with the carrier to resolve
          issues.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Contact</h2>
        <p class="text-sm text-slate-600">
          For customs questions, reach out at customs@dispatch.example with your order number and tracking details.
        </p>
      </section>
    </article>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
</script>
