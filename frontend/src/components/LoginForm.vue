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
  <div class="flex min-h-screen w-full items-center justify-center bg-zinc-100/70 p-4 dark:bg-zinc-900/50">
    
    <Card class="py-0 mx-auto w-full max-w-[900px] overflow-hidden rounded-xl shadow-2xl md:grid md:grid-cols-2 md:min-h-[580px]">
      
      <div class="flex h-full flex-col justify-center bg-background p-8 md:p-12 lg:p-14">
        <div class="mx-auto w-full max-w-[360px] space-y-8">
          
          <div class="flex flex-col space-y-2">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">
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
      </div>

      <div class="relative hidden h-full w-full bg-zinc-900 md:block">
        <img
          src="/login.png"
          alt="Tsuyanki Learning"
          class="absolute inset-0 h-full w-full object-cover opacity-90 transition-transform duration-700 hover:scale-105"
        />
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent z-10"></div>
        <div class="absolute bottom-0 left-0 right-0 z-20 p-12 text-white">
           <h3 class="text-2xl font-bold mb-3 leading-tight">Kuasai Ingatan Anda.</h3>
           <p class="text-white/90 text-sm leading-relaxed">"Metode spaced repetition Tsuyanki dirancang untuk memindahkan pengetahuan ke memori jangka panjang Anda dengan efisien."</p>
        </div>
      </div>

    </Card>
  </div>
</template>