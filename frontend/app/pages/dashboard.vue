<script setup>
import { ref, onMounted } from 'vue'

const user = useState('user')
const token = useCookie('auth_token')
const config = useRuntimeConfig()

if (!token.value) {
  navigateTo('/login')
}

const stats = ref({
  total_invoices: 0,
  total_paid: 0,
  total_unpaid: 0
})
const isLoading = ref(true)

const fetchStats = async () => {
  try {
    const response = await $fetch(`${config.public.apiBase}/invoices/dashboard-stats`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    stats.value = response
  } catch (error) {
    console.error('Gagal mengambil statistik:', error)
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka || 0)
}

const handleLogout = async () => {
  try {
    await $fetch(`${config.public.apiBase}/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` }
    })
  } catch (error) {
    console.error('Error saat logout:', error)
  } finally {
    token.value = null
    user.value = null
    navigateTo('/login')
  }
}

onMounted(() => fetchStats())
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

        <div class="flex items-center space-x-4">
          <NuxtLink to="/clients" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">Daftar Klien</NuxtLink>
          <NuxtLink to="/invoices" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">Daftar Tagihan</NuxtLink>
          <button @click="handleLogout" class="rounded-lg bg-red-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-200 transition-colors">Logout</button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-xl bg-white p-6 shadow-sm border-t-4 border-blue-500 flex flex-col justify-between">
          <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Invoice</h3>
          <p v-if="isLoading" class="text-gray-400 mt-2">Memuat...</p>
          <p v-else class="text-4xl font-bold text-gray-800 mt-2">{{ stats.total_invoices }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm border-t-4 border-green-500 flex flex-col justify-between">
          <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Sudah Dibayar (Pemasukan)</h3>
          <p v-if="isLoading" class="text-gray-400 mt-2">Memuat...</p>
          <p v-else class="text-3xl font-bold text-gray-800 mt-2">{{ formatRupiah(stats.total_paid) }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm border-t-4 border-orange-500 flex flex-col justify-between">
          <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Belum Dibayar (Piutang)</h3>
          <p v-if="isLoading" class="text-gray-400 mt-2">Memuat...</p>
          <p v-else class="text-3xl font-bold text-gray-800 mt-2">{{ formatRupiah(stats.total_unpaid) }}</p>
        </div>
      </div>

    </div>
  </div>
</template>