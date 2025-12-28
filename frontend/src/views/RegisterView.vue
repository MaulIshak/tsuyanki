<script setup>
import RegisterForm from '@/components/RegisterForm.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const handleAuthSuccess = () => {
  router.push('/dashboard')
}

const handleSwitchToLogin = () => {
  router.push('/login')
}
</script>

<template>
  <div class="container relative flex min-h-screen flex-col items-center md:justify-center md:grid lg:max-w-none lg:grid-cols-2 lg:px-0 py-8 md:py-0">
    <div class="relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex">
      <div class="absolute inset-0 bg-zinc-900" />
      <img
          src="/login.png"
          alt="Tsuyanki Learning"
          class="absolute inset-0 h-full w-full object-cover opacity-60 transition-transform duration-700 hover:scale-105"
      />
      <!-- Gradient Overlay -->
       <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/50 to-black/30 z-10" />

      <!-- Logo + Text (matching sidebar) -->
      <div class="relative z-20 flex items-center gap-2">
        <img src="/logo.png" alt="Tsuyanki" class="h-8 w-auto brightness-0 invert" />
        <span class="font-semibold text-lg text-white tracking-tight">Tsuyanki</span>
      </div>
      <div class="relative z-20 mt-auto">
        <blockquote class="space-y-2">
          <p class="text-lg">
            "Start your spaced repetition journey today."
          </p>
          <footer class="text-sm">Tsuyanki Team</footer>
        </blockquote>
      </div>
    </div>
    <div class="flex h-full items-center justify-center p-6 lg:p-8 relative">
      <div class="absolute right-4 top-4 md:right-8 md:top-8">
        <RouterLink to="/about" class="text-sm font-medium text-muted-foreground hover:text-primary underline-offset-4 hover:underline">
          About
        </RouterLink>
      </div>
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px]">
         <!-- Mobile Logo + Text -->
        <div class="flex flex-col items-center gap-3 text-center lg:hidden">
             <div class="flex items-center gap-2">
               <img src="/logo.png" alt="Tsuyanki" class="h-10 w-auto" />
               <span class="font-semibold text-xl tracking-tight">Tsuyanki</span>
             </div>
             <div class="space-y-1">
               <h1 class="text-2xl font-semibold tracking-tight">Create New Account</h1>
               <p class="text-sm text-muted-foreground">Start your journey to build your knowledge base</p>
             </div>
        </div>

        <div class="bg-white/50 backdrop-blur-sm dark:bg-slate-900/50 p-6 sm:p-8 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800">
            <RegisterForm 
              @auth-success="handleAuthSuccess"
              @switch-to-login="handleSwitchToLogin"
            />
        </div>
      </div>
    </div>
  </div>
</template>
