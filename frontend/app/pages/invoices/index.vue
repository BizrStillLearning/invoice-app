<script setup>
import { ref, onMounted } from 'vue'

const token = useCookie('auth_token')
const config = useRuntimeConfig()

if (!token.value) navigateTo('/login')

const invoices = ref([])
const isLoading = ref(true)

const fetchInvoices = async () => {
  try {
    const response = await $fetch(`${config.public.apiBase}/invoices`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    invoices.value = response
  } catch (error) {
    console.error('Gagal mengambil data invoice:', error)
  } finally {
    isLoading.value = false
  }
}

const markAsPaid = async (id) => {
  if (!confirm('Tandai tagihan ini sebagai Lunas?')) return
  try {
    await $fetch(`${config.public.apiBase}/invoices/${id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { status: 'paid' }
    })
    await fetchInvoices()
  } catch (error) {
    alert('Gagal memperbarui status.')
  }
}

const deleteInvoice = async (id) => {
  if (!confirm('Yakin ingin menghapus invoice ini secara permanen?')) return
  try {
    await $fetch(`${config.public.apiBase}/invoices/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchInvoices()
  } catch (error) {
    alert('Gagal menghapus invoice.')
  }
}

const formatRupiah = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka)
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-700',
    unpaid: 'bg-orange-100 text-orange-700',
    paid: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700'
  }
  return colors[status] || colors.draft
}

const markAsUnpaid = async (id) => {
  if (!confirm('Batalkan status Lunas tagihan ini?')) return
  try {
    await $fetch(`${config.public.apiBase}/invoices/${id}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { status: 'unpaid' }
    })
    await fetchInvoices()
  } catch (error) {
    alert('Gagal memperbarui status.')
  }
}

onMounted(() => fetchInvoices())
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="mx-auto max-w-6xl">

      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Daftar Tagihan (Invoice)</h1>
          <p class="text-sm text-gray-500">Pantau semua tagihan yang Anda kirimkan ke klien.</p>
        </div>
        <div class="space-x-3">
          <NuxtLink to="/dashboard" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg shadow-sm hover:bg-gray-50">Kembali</NuxtLink>
          <NuxtLink to="/invoices/create" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">+ Buat Invoice Baru</NuxtLink>
        </div>
      </div>

      <div class="overflow-hidden bg-white border rounded-xl shadow-sm">
        <table class="w-full text-sm text-left text-gray-500">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
          <tr>
            <th scope="col" class="px-6 py-4">No. Invoice</th>
            <th scope="col" class="px-6 py-4">Klien</th>
            <th scope="col" class="px-6 py-4">Status</th>
            <th scope="col" class="px-6 py-4 text-right">Total</th>
            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
          </tr>
          </thead>
          <tbody>
          <tr v-if="isLoading">
            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Memuat data...</td>
          </tr>
          <tr v-for="invoice in invoices" :key="invoice.id" class="border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ invoice.invoice_number }}</td>
            <td class="px-6 py-4">{{ invoice.client?.name || 'Klien Dihapus' }}</td>
            <td class="px-6 py-4">
                <span :class="`px-2.5 py-1 text-xs font-semibold rounded-full uppercase ${getStatusColor(invoice.status)}`">
                  {{ invoice.status }}
                </span>
            </td>
            <td class="px-6 py-4 text-right font-semibold text-gray-900">
              {{ formatRupiah(invoice.total) }}
            </td>
            <td class="px-6 py-4 flex justify-center space-x-2">
              <NuxtLink :to="`/invoices/${invoice.id}`" class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100">
                Lihat
              </NuxtLink>

              <button @click="deleteInvoice(invoice.id)" class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100">
                Hapus
              </button>

              <button v-if="invoice.status !== 'paid'" @click="markAsPaid(invoice.id)" class="px-3 py-1 text-xs font-medium text-green-600 bg-green-50 rounded hover:bg-green-100">
                Lunas
              </button>

              <button v-else @click="markAsUnpaid(invoice.id)" class="px-3 py-1 text-xs font-medium text-orange-600 bg-orange-50 rounded hover:bg-orange-100">
                Batal Lunas
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>