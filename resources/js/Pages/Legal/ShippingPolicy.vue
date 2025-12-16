<template>
  <StorefrontLayout>
    <article class="mx-auto max-w-3xl space-y-8">
      <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Policy</p>
        <h1 class="text-3xl font-semibold text-slate-900">Shipping Policy</h1>
        <p class="text-sm text-slate-600">
          Clear timelines and responsibilities for orders shipping to Côte d’Ivoire.
        </p>
      </header>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Processing & Dispatch</h2>
        <p class="text-sm text-slate-600">
          Orders are processed within 24 hours on business days. Dispatch from suppliers typically occurs within
          2–5 business days unless otherwise stated on the product page.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Delivery Timeframes</h2>
        <ul class="space-y-2 text-sm text-slate-600 list-disc list-inside">
          <li>Standard tracked delivery to Côte d’Ivoire: {{ deliveryWindow }} (estimate).</li>
          <li>Tracking is provided once the supplier ships; updates continue through delivery.</li>
          <li>Peak periods or carrier constraints may extend delivery; we will notify you of delays.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Customs & Duties</h2>
        <p class="text-sm text-slate-600">
          Duties and VAT are shown at checkout when available. Local customs may request ID or additional payment based
          on regulation; we will support you if contacted.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Lost or Damaged Parcels</h2>
        <p class="text-sm text-slate-600">
          If an order arrives damaged or does not arrive within the expected timeframe, contact support with your order
          number and tracking details. We will investigate with the carrier and supplier and issue replacement or refund
          as applicable.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Contact</h2>
        <p class="text-sm text-slate-600">
          For shipping questions, email support@dispatch.example or use the “Track Order” page with your order number
          and email.
        </p>
      </section>
    </article>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'

const deliveryWindow = '7–18 business days'
</script>
