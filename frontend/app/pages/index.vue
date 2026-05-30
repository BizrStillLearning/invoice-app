<script setup>
const token = useCookie('auth_token')
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 text-center">

    <h1 class="mb-4 text-5xl font-extrabold tracking-tight text-blue-600 sm:text-6xl">
      Invoice App
    </h1>
    <p class="mb-8 max-w-lg text-lg text-gray-600">
      Kelola tagihan, pantau pembayaran, dan catat data klien Anda dengan mudah, cepat, dan profesional.
    </p>

    <div v-if="token" class="space-x-4">
      <NuxtLink to="/dashboard"
                class="inline-block rounded-lg bg-blue-600 px-8 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
        Masuk ke Dashboard
      </NuxtLink>
    </div>

    <div v-else class="flex flex-col sm:flex-row gap-4 justify-center">
      <NuxtLink to="/login"
                class="inline-block rounded-lg border border-gray-300 bg-white px-8 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
        Login
      </NuxtLink>
      <NuxtLink to="/register"
                class="inline-block rounded-lg bg-blue-600 px-8 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
        Daftar Gratis
      </NuxtLink>
    </div>

  </div>
</template>