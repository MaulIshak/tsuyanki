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
    toast.error('Login Failed', { description: 'Email and password are required.' })
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
       throw new Error(msg || 'Login failed.')
    }
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('user_data', JSON.stringify(data.user))
    toast.success('Login Successful', { description: `Welcome back.` })
    emit('auth-success')
  } catch (error) {
    toast.error('Error', { description: error.message })
  } finally {
    isLoading.value = false
  }
}

const handleGoogleLogin = () => {
  window.location.href = `${API_BASE_URL}/auth/google/redirect`
}
// ------------------------------------
</script>

<template>
  <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-87.5">
    <div class="hidden lg:flex flex-col space-y-2 text-center">
      <h1 class="text-2xl font-semibold tracking-tight">
        Welcome Back
      </h1>
      <p class="text-sm text-muted-foreground">
        Sign in to continue your learning progress on Tsuyanki.
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
            <a href="#" tabindex="-1" class="text-xs text-primary hover:underline font-medium">Forgot password?</a>
          </div>
        <Input id="password" v-model="password" type="password" placeholder="••••••••" :disabled="isLoading" required class="h-11" />
      </div>

      <Button class="w-full h-11 text-[15px] font-semibold tracking-wide" type="submit" :disabled="isLoading">
        <Loader2 v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" />
        <span v-else>Sign In Now <ArrowRight class="inline ml-1 w-4 h-4" /></span>
      </Button>

      <div class="relative">
        <div class="absolute inset-0 flex items-center">
          <span class="w-full border-t" />
        </div>
        <div class="relative flex justify-center text-xs uppercase">
          <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
        </div>
      </div>

      <Button type="button" variant="outline" class="w-full h-11" :disabled="isLoading" @click="handleGoogleLogin">
        <svg class="mr-2 h-4 w-4" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="google" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512">
          <path fill="currentColor" d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"></path>
        </svg>
        Login with Google
      </Button>
    </form>

    <div class="text-center text-sm text-muted-foreground pt-4">
      Don't have an account? 
      <button @click="$emit('switch-to-register')" class="font-semibold text-primary hover:underline underline-offset-4 transition-colors">
        Sign up free
      </button>
    </div>
  </div>
</template>
