<template>
  <div class="min-h-screen bg-white text-slate-900">
    <header class="border-b border-slate-100 bg-white/90 backdrop-blur">
      <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <Link href="/" class="text-lg font-semibold tracking-tight">
          Dispatch
        </Link>
        <nav class="hidden gap-6 text-sm font-medium sm:flex">
          <Link href="/products" class="hover:text-slate-500">Shop</Link>
          <Link href="/orders/track" class="hover:text-slate-500">Track</Link>
          <Link href="/cart" class="hover:text-slate-500">Cart</Link>
        </nav>
        <div class="flex items-center gap-3">
          <Link
            href="/cart"
            class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold hover:bg-slate-50"
          >
            Cart
          </Link>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pb-16 pt-10 sm:px-6 lg:px-8">
      <slot />
    </main>

    <footer class="border-t border-slate-100 bg-white">
      <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-sm text-slate-500 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <span>Dispatch © {{ new Date().getFullYear() }}</span>
          <div class="flex gap-4">
            <Link href="/orders/track" class="hover:text-slate-700">Track order</Link>
            <Link href="/checkout" class="hover:text-slate-700">Checkout</Link>
          </div>
        </div>
        <p class="text-xs">
          Delivery to Côte d’Ivoire with clear customs disclosure. Duties and VAT are shown at checkout when applicable.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
