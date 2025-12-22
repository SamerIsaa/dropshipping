<template>
  <StorefrontLayout>
    <div class="space-y-8">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Account</p>
          <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Notifications</h1>
          <p class="text-sm text-slate-500">Order updates, shipping alerts, and account messages.</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="btn-secondary text-xs"
            :class="{ 'cursor-not-allowed opacity-60': !unreadCount }"
            :disabled="!unreadCount"
            @click="markAllRead"
          >
            Mark all read
          </button>
          <Link href="/account" class="btn-ghost text-sm">Back to profile</Link>
        </div>
      </div>

      <div v-if="notifications.length" class="space-y-3">
        <article
          v-for="notification in notifications"
          :key="notification.id"
          class="card flex flex-wrap items-start justify-between gap-4 p-5 text-sm"
          :class="notification.read_at ? 'border-slate-100' : 'border-[#29ab87]/30 bg-[#dfff86]/10'"
        >
          <div class="space-y-1">
            <p class="font-semibold text-slate-900">{{ notification.title }}</p>
            <p class="text-slate-600">{{ notification.body }}</p>
            <p class="text-xs text-slate-400">{{ formatDate(notification.created_at) }}</p>
            <a
              v-if="notification.action_url"
              :href="notification.action_url"
              class="inline-flex items-center gap-1 text-xs font-semibold text-[#29ab87] hover:text-[#2aaa8a]"
            >
              {{ notification.action_label || 'View details' }}
              <svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="currentColor">
                <path d="M7 5l5 5-5 5" />
              </svg>
            </a>
          </div>

          <div class="flex items-center gap-2">
            <span
              v-if="!notification.read_at"
              class="rounded-full bg-[#29ab87]/10 px-2 py-1 text-[0.7rem] font-semibold text-[#29ab87]"
            >
              New
            </span>
            <button
              v-if="!notification.read_at"
              type="button"
              class="btn-ghost text-xs"
              @click="markRead(notification.id)"
            >
              Mark read
            </button>
          </div>
        </article>
      </div>
      <div v-else class="card-muted p-6 text-sm text-slate-500">
        No notifications yet. We will alert you when something needs attention.
      </div>
    </div>
  </StorefrontLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue'

const props = defineProps({
  notifications: { type: Array, default: () => [] },
  unreadCount: { type: Number, default: 0 },
})

const markRead = (id) => {
  router.post(`/account/notifications/${id}/read`, {}, { preserveScroll: true })
}

const markAllRead = () => {
  router.post('/account/notifications/read-all', {}, { preserveScroll: true })
}

const formatDate = (value) => {
  if (! value) {
    return ''
  }
  return new Date(value).toLocaleString()
}
</script>
