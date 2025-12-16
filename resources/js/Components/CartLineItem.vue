<template>
  <div class="grid grid-cols-[auto,1fr,auto] items-center gap-4 rounded-xl border border-slate-100 p-3 sm:p-4">
    <div class="h-16 w-16 overflow-hidden rounded-lg bg-slate-50">
      <img
        v-if="line.media?.[0]"
        :src="line.media[0]"
        :alt="line.name"
        class="h-full w-full object-cover"
      />
    </div>
    <div class="space-y-1">
      <p class="text-sm font-semibold text-slate-900">{{ line.name }}</p>
      <p class="text-xs text-slate-500">Qty: {{ line.quantity }}</p>
      <p class="text-xs text-slate-500">Variant: {{ line.variant ?? 'Default' }}</p>
      <div class="text-sm font-semibold text-slate-900">
        {{ currency }} {{ (line.price * line.quantity).toFixed(2) }}
      </div>
    </div>
    <button
      type="button"
      class="text-xs font-semibold text-rose-500 hover:text-rose-600"
      @click="$emit('remove', line.id)"
    >
      Remove
    </button>
  </div>
</template>

<script setup>
defineProps({
  line: { type: Object, required: true },
  currency: { type: String, default: 'USD' },
})

defineEmits(['remove'])
</script>
