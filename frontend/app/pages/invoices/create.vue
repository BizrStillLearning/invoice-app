<script setup>
import { ref, computed, onMounted } from 'vue'

const token = useCookie('auth_token')
const config = useRuntimeConfig()

if (!token.value) navigateTo('/login')

const clients = ref([])
const isLoading = ref(false)

const form = ref({
  client_id: '',
  invoice_number: `INV-${Date.now()}`,
  issue_date: new Date().toISOString().split('T')[0],
  due_date: '',
  status: 'draft',
  notes: '',
  tax: 0
})

const items = ref([
  { description: '', quantity: 1, unit_price: 0 }
])

const subtotal = computed(() => {
  return items.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
})

const grandTotal = computed(() => {
  return subtotal.value + Number(form.value.tax)
})

const addItemRow = () => {
  items.value.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItemRow = (index) => {
  if (items.value.length > 1) {
    items.value.splice(index, 1)
  }
}

const fetchClients = async () => {
  try {
    const response = await $fetch(`${config.public.apiBase}/clients`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    clients.value = response
  } catch (error) {
    console.error('Gagal memuat klien', error)
  }
}

const saveInvoice = async () => {
  isLoading.value = true

  if (!form.value.client_id) {
    alert('Pilih klien terlebih dahulu!')
    isLoading.value = false
    return
  }

  const payload = {
    ...form.value,
    items: items.value
  }

  try {
    await $fetch(`${config.public.apiBase}/invoices`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      },
      body: payload
    })

    navigateTo('/invoices')
  } catch (error) {
    alert('Gagal menyimpan invoice. Periksa kembali form Anda.')
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => fetchClients())
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="mx-auto max-w-5xl">

      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Buat Invoice Baru</h1>
        <NuxtLink to="/invoices" class="text-sm font-medium text-gray-500 hover:text-gray-700">Batal & Kembali</NuxtLink>
      </div>

      <form @submit.prevent="saveInvoice" class="space-y-6">

        <div class="bg-white p-6 rounded-xl shadow-sm border space-y-4">
          <h2 class="text-lg font-semibold border-b pb-2 mb-4">Informasi Tagihan</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Klien</label>
              <select v-model="form.client_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="" disabled>-- Pilih Klien --</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Invoice</label>
              <input v-model="form.invoice_number" type="text" required class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50 font-mono">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Rilis</label>
              <input v-model="form.issue_date" type="date" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
              <input v-model="form.due_date" type="date" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
              <select v-model="form.status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="draft">Draft (Konsep)</option>
                <option value="unpaid">Unpaid (Belum Dibayar)</option>
                <option value="paid">Paid (Lunas)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
          <h2 class="text-lg font-semibold border-b pb-2 mb-4">Detail Barang / Layanan</h2>

          <div class="space-y-3">
            <div class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-500 uppercase px-2">
              <div class="col-span-5">Deskripsi</div>
              <div class="col-span-2">Qty</div>
              <div class="col-span-3">Harga Satuan (Rp)</div>
              <div class="col-span-2 text-right">Aksi</div>
            </div>

            <div v-for="(item, index) in items" :key="index" class="grid grid-cols-12 gap-2 items-center bg-gray-50 p-2 rounded-lg">
              <div class="col-span-5">
                <input v-model="item.description" type="text" placeholder="Nama layanan..." required class="w-full border rounded-md px-2 py-1.5 text-sm">
              </div>
              <div class="col-span-2">
                <input v-model="item.quantity" type="number" min="1" required class="w-full border rounded-md px-2 py-1.5 text-sm">
              </div>
              <div class="col-span-3">
                <input v-model="item.unit_price" type="number" min="0" required class="w-full border rounded-md px-2 py-1.5 text-sm">
              </div>
              <div class="col-span-2 text-right">
                <button type="button" @click="removeItemRow(index)" :disabled="items.length === 1" class="text-red-500 hover:text-red-700 text-sm font-medium disabled:opacity-50">
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <button type="button" @click="addItemRow" class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
            + Tambah Baris Baru
          </button>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border flex flex-col md:flex-row justify-between items-start md:items-end">
          <div class="w-full md:w-1/2 mb-4 md:mb-0">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
            <textarea v-model="form.notes" rows="3" placeholder="Terima kasih atas kerja samanya..." class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
          </div>

          <div class="w-full md:w-1/3 bg-gray-50 p-4 rounded-lg space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Subtotal:</span>
              <span class="font-medium">Rp {{ subtotal.toLocaleString('id-ID') }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 items-center">
              <span>Pajak (Rp):</span>
              <input v-model="form.tax" type="number" min="0" class="w-1/2 border rounded-md px-2 py-1 text-right text-sm">
            </div>
            <div class="flex justify-between text-lg font-bold text-gray-900 border-t pt-2 mt-2">
              <span>Total Tagihan:</span>
              <span>Rp {{ grandTotal.toLocaleString('id-ID') }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button type="submit" :disabled="isLoading" class="px-8 py-3 font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 disabled:opacity-70 text-lg transition">
            {{ isLoading ? 'Menyimpan...' : 'Simpan Invoice' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>