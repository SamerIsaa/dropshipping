<template>
  <StorefrontLayout>
    <article class="mx-auto max-w-3xl space-y-8">
      <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Policy</p>
        <h1 class="text-3xl font-semibold text-slate-900">Privacy Policy</h1>
        <p class="text-sm text-slate-600">
          How we collect, use, and protect your data when you shop with us.
        </p>
      </header>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Data We Collect</h2>
        <ul class="space-y-2 text-sm text-slate-600 list-disc list-inside">
          <li>Contact info: name, email, phone.</li>
          <li>Order details: items, totals, shipping address.</li>
          <li>Technical: device/browser info for security and performance.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">How We Use Data</h2>
        <ul class="space-y-2 text-sm text-slate-600 list-disc list-inside">
          <li>Process and fulfill your orders.</li>
          <li>Provide shipping updates and support.</li>
          <li>Improve site performance and prevent fraud.</li>
        </ul>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Sharing</h2>
        <p class="text-sm text-slate-600">
          We share data with payment processors, suppliers, and carriers solely to fulfill your order. We do not sell
          personal data.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Security</h2>
        <p class="text-sm text-slate-600">
          We use industry-standard security controls and limit access to authorized staff. Payment details are handled by
          certified processors.
        </p>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Your Choices</h2>
        <p class="text-sm text-slate-600">
          You can request data access or deletion where applicable. Contact privacy@dispatch.example with your request.
        </p>
      </section>
    </article>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
</script>
