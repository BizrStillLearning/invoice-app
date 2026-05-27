<script setup>
import { ref } from 'vue'

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

const config = useRuntimeConfig()

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await $fetch(`${config.public.apiBase}/login`, {
      method: 'POST',
      body: {
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
      errorMessage.value = 'Terjadi kesalahan saat login.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md space-y-8 rounded-2xl bg-white p-8 shadow-xl">

      <!-- Header Form -->
      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-gray-900">Masuk ke Akun</h2>
        <p class="mt-2 text-sm text-gray-600">
          Atau <NuxtLink to="/register" class="font-medium text-blue-600 hover:text-blue-500">buat akun baru</NuxtLink>
        </p>
      </div>

      <!-- Pesan Error -->
      <div v-if="errorMessage" class="rounded-lg bg-red-50 p-4 text-sm text-red-600">
        {{ errorMessage }}
      </div>

      <!-- Form Login -->
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4 rounded-md shadow-sm">
          <div>
            <label for="email" class="sr-only">Alamat Email</label>
            <input v-model="email" id="email" name="email" type="email" required
                   class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                   placeholder="Alamat Email">
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input v-model="password" id="password" name="password" type="password" required
                   class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                   placeholder="Password">
          </div>
        </div>

        <div>
          <button type="submit" :disabled="isLoading"
                  class="group relative flex w-full justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-70">
            <span v-if="isLoading">Memproses...</span>
            <span v-else>Masuk</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</template>