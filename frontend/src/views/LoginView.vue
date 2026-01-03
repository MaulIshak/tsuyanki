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
  <div class="relative flex min-h-screen flex-col items-center justify-center md:grid lg:max-w-none lg:grid-cols-2 lg:px-0 lg:h-screen lg:overflow-hidden">
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
            "Mastering a language is a journey of a thousand cards."
          </p>
          <footer class="text-sm">Tsuyanki Team</footer>
        </blockquote>
      </div>
    </div>
    <div class="flex h-full w-full items-center justify-center p-4 lg:p-8 relative">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px] md:w-[400px]">
        <!-- Mobile Logo + Text -->
        <div class="flex flex-col items-center gap-3 text-center lg:hidden">
             <div class="flex items-center gap-2">
               <img src="/logo.png" alt="Tsuyanki" class="h-10 w-auto" />
               <span class="font-semibold text-xl tracking-tight">Tsuyanki</span>
             </div>
             <div class="space-y-1">
               <h1 class="text-2xl font-semibold tracking-tight">Welcome Back</h1>
               <p class="text-sm text-muted-foreground">Sign in to continue your learning progress</p>
             </div>
        </div>

        <div class="p-0 lg:bg-white/50 lg:backdrop-blur-sm lg:dark:bg-slate-900/50 lg:p-8 lg:rounded-xl lg:shadow-lg lg:border lg:border-slate-200 lg:dark:border-slate-800">
             <LoginForm 
              @auth-success="handleAuthSuccess"
              @switch-to-register="handleSwitchToRegister"
            />
        </div>
      </div>
    </div>
  </div>
</template>
