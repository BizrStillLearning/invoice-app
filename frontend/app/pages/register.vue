<script setup>
import { ref } from 'vue'

const name = ref('')
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

const config = useRuntimeConfig()

const handleRegister = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await $fetch(`${config.public.apiBase}/register`, {
      method: 'POST',
      body: {
        name: name.value,
        email: email.value,
        password: password.value
      }
    })

    const token = useCookie('auth_token')
    token.value = response.access_token

    const userState = useState('user', () => null)
    userState.value = response.user

    navigateTo('/dashboard')

  } catch (error) {
    if (error.response?._data?.message) {
      errorMessage.value = error.response._data.message
    } else {
      errorMessage.value = 'Terjadi kesalahan saat mendaftar.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md space-y-8 rounded-2xl bg-white p-8 shadow-xl">

      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-gray-900">Buat Akun</h2>
        <p class="mt-2 text-sm text-gray-600">
          Sudah punya akun? <NuxtLink to="/login" class="font-medium text-blue-600 hover:text-blue-500">Masuk di sini</NuxtLink>
        </p>
      </div>

      <div v-if="errorMessage" class="rounded-lg bg-red-50 p-4 text-sm text-red-600">
        {{ errorMessage }}
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="space-y-4 rounded-md shadow-sm">
          <div>
            <label for="name" class="sr-only">Nama Lengkap</label>
            <input v-model="name" id="name" type="text" required
                   class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                   placeholder="Nama Lengkap">
          </div>
          <div>
            <label for="email" class="sr-only">Alamat Email</label>
            <input v-model="email" id="email" type="email" required
                   class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                   placeholder="Alamat Email">
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input v-model="password" id="password" type="password" required minlength="8"
                   class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                   placeholder="Password (minimal 8 karakter)">
          </div>
        </div>

        <div>
          <button type="submit" :disabled="isLoading"
                  class="group relative flex w-full justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70">
            <span v-if="isLoading">Memproses...</span>
            <span v-else>Daftar Sekarang</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</template>