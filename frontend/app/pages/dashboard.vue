<script setup>
const user = useState('user')
const token = useCookie('auth_token')
const config = useRuntimeConfig()

if (!token.value) {
  navigateTo('/login')
}

const handleLogout = async () => {
  try {
    await $fetch(`${config.public.apiBase}/logout`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
  } catch (error) {
    console.error('Error saat logout:', error)
  } finally {
    token.value = null
    user.value = null
    navigateTo('/login')
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="mx-auto max-w-4xl">

      <div class="flex items-center justify-between rounded-xl bg-white p-6 shadow-sm mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Invoice App Dashboard</h1>
          <p class="text-gray-500 mt-1">
            Selamat datang kembali, <span class="font-semibold text-blue-600">{{ user?.name || 'Pengguna' }}</span>!
          </p>
        </div>

        <button @click="handleLogout"
                class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-200 transition-colors">
          Logout
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-xl bg-white p-6 shadow-sm border-t-4 border-blue-500">
          <h3 class="text-gray-500 text-sm font-medium">Total Invoice</h3>
          <p class="text-3xl font-bold text-gray-800 mt-2">0</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm border-t-4 border-green-500">
          <h3 class="text-gray-500 text-sm font-medium">Sudah Dibayar</h3>
          <p class="text-3xl font-bold text-gray-800 mt-2">Rp 0</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm border-t-4 border-orange-500">
          <h3 class="text-gray-500 text-sm font-medium">Belum Dibayar</h3>
          <p class="text-3xl font-bold text-gray-800 mt-2">Rp 0</p>
        </div>
      </div>

    </div>
  </div>
</template>