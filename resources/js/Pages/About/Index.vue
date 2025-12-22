<template>
  <StorefrontLayout>
    <div class="mx-auto max-w-3xl space-y-8">
      <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">{{ t('About') }}</p>
        <h1 class="text-3xl font-semibold text-slate-900">{{ t('Built for cross-border clarity') }}</h1>
        <p class="text-sm text-slate-600">
          {{ t("Azura helps customers in Cote d'Ivoire access global products with predictable delivery and transparent customs. We coordinate suppliers, track orders, and keep you informed.") }}
        </p>
      </header>

      <section class="grid gap-6 sm:grid-cols-2">
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('Our promise') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('We disclose duties before payment, monitor fulfillment, and provide tracking from dispatch to delivery.') }}
          </p>
        </div>
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('Local support') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('Support is available via WhatsApp or email with local context and fast resolution.') }}
          </p>
        </div>
      </section>

      <section class="card-muted p-6 text-sm text-slate-600">
        {{ t('We partner with vetted suppliers and prioritize products with reliable logistics. Each order is reviewed to ensure a smooth delivery experience.') }}
      </section>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import { useTranslations } from '@/i18n'

const { t } = useTranslations()
</script>
