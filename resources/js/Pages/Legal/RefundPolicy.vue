<template>
  <StorefrontLayout>
    <article class="mx-auto max-w-3xl space-y-8">
      <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Policy</p>
        <h1 class="text-3xl font-semibold text-slate-900">Refund Policy</h1>
        <p class="text-sm text-slate-600">
          How we handle cancellations, returns, and refunds for orders shipped to Côte d’Ivoire.
        </p>
      </header>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Eligibility</h2>
        <ul class="space-y-2 text-sm text-slate-600 list-disc list-inside">
          <li>Damaged on arrival with photo evidence.</li>
          <li>Incorrect item or missing items.</li>
          <li>Delivery not completed within the stated window after investigation.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Process</h2>
        <ol class="space-y-2 text-sm text-slate-600 list-decimal list-inside">
          <li>Submit a request with order number, photos (if damaged), and tracking info.</li>
          <li>We investigate with the supplier/carrier and confirm eligibility.</li>
          <li>Refunds are issued to the original payment method; processing times vary by provider.</li>
        </ol>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Exclusions</h2>
        <p class="text-sm text-slate-600">
          Requests outside the return window, items used/worn, or cases where customs duties were unpaid and shipment was
          returned may be excluded unless required by law.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Partial Refunds</h2>
        <p class="text-sm text-slate-600">
          For partial item issues, we may refund the affected items only. Shipping may be refunded if the entire order
          fails delivery.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Contact</h2>
        <p class="text-sm text-slate-600">
          Email support@dispatch.example with your order number and details to start a refund request.
        </p>
      </section>
    </article>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
</script>
