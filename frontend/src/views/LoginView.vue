<script setup>
import LoginForm from '@/components/LoginForm.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const handleAuthSuccess = () => {
  router.push('/dashboard')
}

const handleSwitchToRegister = () => {
  router.push('/register')
}
</script>

<template>
  <div class="container relative flex h-screen flex-col items-center justify-center md:grid lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex">
      <div class="absolute inset-0 bg-zinc-900" />
      <img
          src="/login.png"
          alt="Tsuyanki Learning"
          class="absolute inset-0 h-full w-full object-cover opacity-60 transition-transform duration-700 hover:scale-105"
      />
      <!-- Gradient Overlay -->
       <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/50 to-black/30 z-10" />

        <img src="/logo.png" alt="Tsuyanki" class="h-10 w-auto" />
      <div class="relative z-20 mt-auto">
        <blockquote class="space-y-2">
          <p class="text-lg">
            "Mastering a language is a journey of a thousand cards."
          </p>
          <footer class="text-sm">Tsuyanki Team</footer>
        </blockquote>
      </div>
    </div>
    <div class="flex h-full items-center justify-center p-6 lg:p-8">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px]">
        <!-- Mobile Logo -->
        <div class="flex flex-col items-center gap-2 text-center lg:hidden">
             <img src="/logo.png" alt="Tsuyanki" class="h-12 w-auto" />
             <h1 class="text-2xl font-semibold tracking-tight">Welcome back</h1>
             <p class="text-sm text-muted-foreground">Enter your credentials to access your account</p>
        </div>

        <div class="bg-white/50 backdrop-blur-sm dark:bg-slate-900/50 p-6 sm:p-8 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800">
             <LoginForm 
              @auth-success="handleAuthSuccess"
              @switch-to-register="handleSwitchToRegister"
            />
        </div>
      </div>
    </div>
  </div>
</template>
