<script setup>
import { ref } from 'vue'
import { toast } from 'vue-sonner'
import { Loader2, ArrowRight } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card } from '@/components/ui/card'

const emit = defineEmits(['switch-to-login', 'auth-success'])

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

const handleRegister = async () => {
  isLoading.value = true
  if (!name.value || !email.value || !password.value) {
    toast.error('Data Tidak Lengkap', { description: 'Semua kolom wajib diisi.' })
    isLoading.value = false; return
  }
  if (password.value !== confirmPassword.value) {
    toast.error('Password Salah', { description: 'Konfirmasi password tidak cocok.' })
    confirmPassword.value = ''; isLoading.value = false; return
  }
  try {
    const response = await fetch(`${API_BASE_URL}/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name: name.value, email: email.value, password: password.value, password_confirmation: confirmPassword.value }),
    })
    const data = await response.json()
    if (!response.ok) {
       const msg = response.status === 422 ? Object.values(data.errors)[0][0] : data.message
       throw new Error(msg || 'Registrasi gagal.')
    }
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('user_data', JSON.stringify(data.user))
    toast.success('Akun Dibuat', { description: `Selamat datang di Tsuyanki, ${data.user.name}.` })
    emit('auth-success')
  } catch (error) {
    toast.error('Gagal', { description: error.message })
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-87.5">
    <div class="flex flex-col space-y-2 text-center">
      <h1 class="text-2xl font-semibold tracking-tight">
        Buat Akun Baru
      </h1>
      <p class="text-sm text-muted-foreground">
        Mulai perjalanan membangun basis pengetahuan Anda.
      </p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <div class="space-y-2">
        <label for="name" class="text-sm font-medium">Nama Lengkap</label>
        <Input id="name" v-model="name" type="text" placeholder="Contoh: Budi Santoso" :disabled="isLoading" required class="h-11" />
      </div>
      <div class="space-y-2">
        <label for="email" class="text-sm font-medium">Email</label>
        <Input id="email" v-model="email" type="email" placeholder="nama@email.com" :disabled="isLoading" required class="h-11" />
      </div>
      <div class="space-y-2">
          <label for="password" class="text-sm font-medium">Password</label>
        <Input id="password" v-model="password" type="password" placeholder="Minimal 8 karakter" :disabled="isLoading" required class="h-11" />
      </div>
        <div class="space-y-2">
          <label for="confirmPassword" class="text-sm font-medium">Ulangi Password</label>
        <Input id="confirmPassword" v-model="confirmPassword" type="password" placeholder="••••••••" :disabled="isLoading" required class="h-11" />
      </div>

      <Button class="w-full h-11 text-[15px] font-semibold tracking-wide mt-2" type="submit" :disabled="isLoading">
        <Loader2 v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" />
        <span v-else>Daftar Akun <ArrowRight class="inline ml-1 w-4 h-4" /></span>
      </Button>
    </form>

    <div class="text-center text-sm text-muted-foreground pt-2">
        Sudah punya akun? 
        <button @click="$emit('switch-to-login')" class="font-semibold text-primary hover:underline underline-offset-4 transition-colors">
          Login di sini
        </button>
        <p class="mt-4 text-xs px-2 text-muted-foreground/80 leading-relaxed">
        Dengan mendaftar, Anda menyetujui <a href="#" class="underline hover:text-primary">Syarat & Ketentuan</a> kami.
      </p>
    </div>
  </div>
</template>
