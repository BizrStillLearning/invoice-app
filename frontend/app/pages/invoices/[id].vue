<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const token = useCookie('auth_token')
const config = useRuntimeConfig()
const user = useState('user')

if (!token.value) navigateTo('/login')

const invoice = ref(null)
const isLoading = ref(true)

const fetchInvoiceDetail = async () => {
  try {
    const response = await $fetch(`${config.public.apiBase}/invoices/${route.params.id}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    invoice.value = response
  } catch (error) {
    console.error('Gagal mengambil detail invoice', error)
    alert('Invoice tidak ditemukan!')
    navigateTo('/invoices')
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (angka) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka)
}

const printInvoice = () => {
  window.print()
}

onMounted(() => fetchInvoiceDetail())
</script>

<template>
  <div class="min-h-screen bg-gray-200 py-8 print:py-0 print:bg-white">
    <div class="mx-auto max-w-4xl">

      <div class="mb-6 flex justify-between items-center print:hidden px-4 sm:px-0">
        <NuxtLink to="/invoices" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg shadow-sm hover:bg-gray-50">
          ← Kembali
        </NuxtLink>
        <button @click="printInvoice" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
          🖨️ Cetak / Simpan PDF
        </button>
      </div>

      <div v-if="!isLoading && invoice" class="bg-white p-10 sm:p-16 shadow-xl sm:rounded-xl print:shadow-none print:rounded-none">

        <div class="flex justify-between items-start border-b pb-8">
          <div>
            <h1 class="text-4xl font-extrabold text-blue-600 tracking-tight">INVOICE</h1>
            <p class="text-gray-500 mt-1 font-mono">{{ invoice.invoice_number }}</p>
          </div>
          <div class="text-right">
            <h2 class="font-bold text-gray-800 text-xl">{{ user?.name }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ user?.email }}</p>
          </div>
        </div>

        <div class="flex justify-between mt-8">
          <div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Ditagihkan Kepada:</p>
            <h3 class="font-bold text-gray-800 text-lg">{{ invoice.client?.name }}</h3>
            <p class="text-gray-600">{{ invoice.client?.address || 'Alamat tidak tersedia' }}</p>
            <p class="text-gray-600 mt-1" v-if="invoice.client?.npwp">NPWP: {{ invoice.client.npwp }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600"><span class="font-semibold text-gray-800">Tanggal:</span> {{ invoice.issue_date }}</p>
            <p class="text-sm text-gray-600 mt-1"><span class="font-semibold text-gray-800">Jatuh Tempo:</span> {{ invoice.due_date }}</p>
            <div class="mt-4 inline-block px-3 py-1 rounded text-sm font-bold uppercase tracking-wider border-2"
                 :class="invoice.status === 'paid' ? 'border-green-500 text-green-600' : 'border-orange-500 text-orange-600'">
              {{ invoice.status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}
            </div>
          </div>
        </div>

        <div class="mt-10">
          <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-100 text-gray-700 text-sm uppercase">
              <th class="py-3 px-4 font-semibold">Deskripsi Layanan</th>
              <th class="py-3 px-4 font-semibold text-center">Qty</th>
              <th class="py-3 px-4 font-semibold text-right">Harga</th>
              <th class="py-3 px-4 font-semibold text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in invoice.items" :key="item.id" class="border-b text-gray-800">
              <td class="py-4 px-4">{{ item.description }}</td>
              <td class="py-4 px-4 text-center">{{ item.quantity }}</td>
              <td class="py-4 px-4 text-right">{{ formatRupiah(item.unit_price) }}</td>
              <td class="py-4 px-4 text-right font-medium">{{ formatRupiah(item.total) }}</td>
            </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end mt-8">
          <div class="w-full sm:w-1/2 lg:w-1/3 space-y-3">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal</span>
              <span>{{ formatRupiah(invoice.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600" v-if="invoice.tax > 0">
              <span>Pajak</span>
              <span>{{ formatRupiah(invoice.tax) }}</span>
            </div>
            <div class="flex justify-between border-t-2 border-gray-800 pt-3 text-lg font-bold text-gray-900">
              <span>Total Tagihan</span>
              <span>{{ formatRupiah(invoice.total) }}</span>
            </div>
          </div>
        </div>

        <div class="mt-16 border-t pt-8" v-if="invoice.notes">
          <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Catatan:</p>
          <p class="text-gray-600 text-sm whitespace-pre-wrap">{{ invoice.notes }}</p>
        </div>

      </div>
    </div>
  </div>
</template>