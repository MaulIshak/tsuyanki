<script setup>
import { ref } from 'vue'
import { toast } from 'vue-sonner'
import { Loader2, ArrowRight } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card } from '@/components/ui/card'

const emit = defineEmits(['switch-to-register', 'auth-success'])

const email = ref('')
const password = ref('')
const isLoading = ref(false)

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

const handleLogin = async () => {
  isLoading.value = true
  if (!email.value || !password.value) {
    toast.error('Gagal Login', { description: 'Email dan password dibutuhkan.' })
    isLoading.value = false; return
  }
  try {
    const response = await fetch(`${API_BASE_URL}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })
    const data = await response.json()
    if (!response.ok) {
       const msg = response.status === 422 ? Object.values(data.errors)[0][0] : data.message
       throw new Error(msg || 'Login gagal.')
    }
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('user_data', JSON.stringify(data.user))
    toast.success('Berhasil Masuk', { description: `Selamat datang kembali.` })
    emit('auth-success')
  } catch (error) {
    toast.error('Error', { description: error.message })
  } finally {
    isLoading.value = false
  }
}
// ------------------------------------
</script>

<template>
  <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
    <div class="flex flex-col space-y-2 text-center">
      <h1 class="text-2xl font-semibold tracking-tight">
        Selamat Datang
      </h1>
      <p class="text-sm text-muted-foreground">
        Masuk untuk melanjutkan progres belajar Anda di Tsuyanki.
      </p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-5">
      <div class="space-y-2">
        <label for="email" class="text-sm font-medium">Email</label>
        <Input id="email" v-model="email" type="email" placeholder="nama@email.com" :disabled="isLoading" required class="h-11" />
      </div>

      <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label for="password" class="text-sm font-medium">Password</label>
            <a href="#" tabindex="-1" class="text-xs text-primary hover:underline font-medium">Lupa password?</a>
          </div>
        <Input id="password" v-model="password" type="password" placeholder="••••••••" :disabled="isLoading" required class="h-11" />
      </div>

      <Button class="w-full h-11 text-[15px] font-semibold tracking-wide" type="submit" :disabled="isLoading">
        <Loader2 v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" />
        <span v-else>Masuk Sekarang <ArrowRight class="inline ml-1 w-4 h-4" /></span>
      </Button>
    </form>

    <div class="text-center text-sm text-muted-foreground pt-4">
      Belum memiliki akun? 
      <button @click="$emit('switch-to-register')" class="font-semibold text-primary hover:underline underline-offset-4 transition-colors">
        Daftar gratis
      </button>
    </div>
  </div>
</template>
