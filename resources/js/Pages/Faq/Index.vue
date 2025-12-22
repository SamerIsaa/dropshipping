<template>
  <StorefrontLayout>
    <div class="mx-auto max-w-3xl space-y-8">
      <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">{{ t('FAQ') }}</p>
        <h1 class="text-3xl font-semibold text-slate-900">{{ t('Answers to common questions') }}</h1>
        <p class="text-sm text-slate-600">
          {{ t('Delivery timelines, customs, and payment information for cross-border orders.') }}
        </p>
      </header>

      <div class="space-y-4">
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('How long does delivery take?') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('Most orders arrive in 7 to 18 business days after dispatch. You will receive tracking within 24 to 48 hours.') }}
          </p>
        </div>
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('Will I pay customs duties?') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('Duties and VAT are disclosed before checkout when available. If customs requests extra documentation, we will contact you.') }}
          </p>
        </div>
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('What if my order is damaged?') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('Contact support within 7 days of delivery with photos and your order number. We will arrange a replacement or refund based on the issue.') }}
          </p>
        </div>
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('Can I change my address?') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('Address changes are possible within 2 hours of placing the order. Contact support immediately.') }}
          </p>
        </div>
        <div class="card p-6">
          <h2 class="text-sm font-semibold text-slate-900">{{ t('Do you offer refunds?') }}</h2>
          <p class="mt-2 text-sm text-slate-600">
            {{ t('Yes. Review the refund policy for eligible cases and timelines.') }}
          </p>
        </div>
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'
import { useTranslations } from '@/i18n'

const { t } = useTranslations()
</script>
