<script setup>
import { ref, onMounted } from 'vue'

const token = useCookie('auth_token')
const config = useRuntimeConfig()

if (!token.value) {
  navigateTo('/login')
}

const clients = ref([])
const isLoading = ref(false)
const isModalOpen = ref(false)
const isEditMode = ref(false)
const editId = ref(null)

const isDetailModalOpen = ref(false)
const selectedClient = ref(null)

const form = ref({
  name: '', email: '', phone: '', address: '', npwp: '', pic_name: '', logo: null
})

const fetchClients = async () => {
  try {
    const response = await $fetch(`${config.public.apiBase}/clients`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    clients.value = response
  } catch (error) {
    console.error('Gagal mengambil data klien:', error)
  }
}

const handleFileChange = (event) => {
  form.value.logo = event.target.files[0]
}

const openAddModal = () => {
  isEditMode.value = false
  editId.value = null
  form.value = { name: '', email: '', phone: '', address: '', npwp: '', pic_name: '', logo: null }
  isModalOpen.value = true
}

const openEditModal = (client) => {
  isEditMode.value = true
  editId.value = client.id
  form.value = {
    name: client.name, email: client.email || '', phone: client.phone || '',
    address: client.address || '', npwp: client.npwp || '', pic_name: client.pic_name || '', logo: null
  }
  isModalOpen.value = true
}

const openDetailModal = (client) => {
  selectedClient.value = client
  isDetailModalOpen.value = true
}

const saveClient = async () => {
  isLoading.value = true
  const formData = new FormData()

  formData.append('name', form.value.name)
  if (form.value.email) formData.append('email', form.value.email)
  if (form.value.phone) formData.append('phone', form.value.phone)
  if (form.value.address) formData.append('address', form.value.address)
  if (form.value.npwp) formData.append('npwp', form.value.npwp)
  if (form.value.pic_name) formData.append('pic_name', form.value.pic_name)
  if (form.value.logo) formData.append('logo', form.value.logo)

  let url = `${config.public.apiBase}/clients`
  let method = 'POST'

  if (isEditMode.value) {
    url = `${config.public.apiBase}/clients/${editId.value}`
    formData.append('_method', 'PUT')
  }

  try {
    await $fetch(url, {
      method: method,
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData
    })
    isModalOpen.value = false
    await fetchClients()
  } catch (error) {
    alert('Gagal menyimpan klien.')
  } finally {
    isLoading.value = false
  }
}

const deleteClient = async (id) => {
  if (!confirm('Yakin ingin menghapus klien ini?')) return
  try {
    await $fetch(`${config.public.apiBase}/clients/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchClients()
  } catch (error) {
    console.error('Gagal menghapus klien:', error)
  }
}

onMounted(() => fetchClients())
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="mx-auto max-w-6xl">

      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Manajemen Klien</h1>
          <p class="text-sm text-gray-500">Kelola data pelanggan untuk tagihan Anda.</p>
        </div>
        <div class="space-x-3">
          <NuxtLink to="/dashboard" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg shadow-sm hover:bg-gray-50">
            Kembali
          </NuxtLink>
          <button @click="openAddModal" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
            + Tambah Klien
          </button>
        </div>
      </div>

      <div class="overflow-hidden bg-white border rounded-xl shadow-sm">
        <table class="w-full text-sm text-left text-gray-500">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
          <tr>
            <th scope="col" class="px-6 py-4">Klien / Perusahaan</th>
            <th scope="col" class="px-6 py-4">Kontak (PIC)</th>
            <th scope="col" class="px-6 py-4">NPWP</th>
            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
          </tr>
          </thead>
          <tbody>
          <tr v-if="clients.length === 0">
            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data klien.</td>
          </tr>
          <tr v-for="client in clients" :key="client.id" class="border-b hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="font-medium text-gray-900">{{ client.name }}</div>
              <div class="text-xs text-gray-500">{{ client.email || '-' }}</div>
            </td>
            <td class="px-6 py-4">{{ client.pic_name || '-' }}</td>
            <td class="px-6 py-4 font-mono text-xs">{{ client.npwp || '-' }}</td>
            <td class="px-6 py-4 flex justify-center space-x-2">

              <button @click="openDetailModal(client)" class="p-1.5 text-gray-500 bg-gray-100 rounded hover:bg-gray-200 hover:text-gray-700 transition" title="Detail">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              </button>

              <button @click="openEditModal(client)" class="p-1.5 text-blue-600 bg-blue-50 rounded hover:bg-blue-100 hover:text-blue-800 transition" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
              </button>

              <button @click="deleteClient(client.id)" class="p-1.5 text-red-600 bg-red-50 rounded hover:bg-red-100 hover:text-red-800 transition" title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>

            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <div v-if="isDetailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900">Detail Klien</h3>
            <button @click="isDetailModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>
          <div class="p-6 space-y-3 text-sm">
            <p><span class="font-medium text-gray-500 block">Nama Perusahaan:</span> <span class="text-gray-900 text-base">{{ selectedClient?.name }}</span></p>
            <p><span class="font-medium text-gray-500 block">PIC / Kontak:</span> {{ selectedClient?.pic_name || '-' }}</p>
            <p><span class="font-medium text-gray-500 block">Email:</span> {{ selectedClient?.email || '-' }}</p>
            <p><span class="font-medium text-gray-500 block">Telepon:</span> {{ selectedClient?.phone || '-' }}</p>
            <p><span class="font-medium text-gray-500 block">NPWP:</span> <span class="font-mono">{{ selectedClient?.npwp || '-' }}</span></p>
            <p><span class="font-medium text-gray-500 block">Alamat Lengkap:</span> {{ selectedClient?.address || '-' }}</p>
          </div>
          <div class="bg-gray-50 px-6 py-3 border-t text-right">
            <button @click="isDetailModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Tutup</button>
          </div>
        </div>
      </div>

      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900">{{ isEditMode ? 'Edit Klien' : 'Tambah Klien Baru' }}</h3>
            <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>

          <form @submit.prevent="saveClient" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-4">
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700">Nama Perusahaan / Klien *</label>
                  <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700">Email</label>
                  <input v-model="form.email" type="email" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700">Telepon</label>
                  <input v-model="form.phone" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700">Nama PIC (Kontak)</label>
                  <input v-model="form.pic_name" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700">NPWP</label>
                  <input v-model="form.npwp" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                  <label class="block mb-1 text-sm font-medium text-gray-700">Logo Perusahaan (Opsional)</label>
                  <input type="file" accept="image/*" @change="handleFileChange" class="w-full px-3 py-1.5 border rounded-lg text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:border-0 file:rounded-md file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
              </div>

              <div class="md:col-span-2 mt-2">
                <label class="block mb-1 text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea v-model="form.address" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3 pt-4 border-t">
              <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
              <button type="submit" :disabled="isLoading" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-70">
                {{ isLoading ? 'Menyimpan...' : (isEditMode ? 'Simpan Perubahan' : 'Simpan Klien') }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>