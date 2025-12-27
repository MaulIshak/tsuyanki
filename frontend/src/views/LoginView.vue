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
       <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-black/30 z-10" />

      <div class="relative z-20 flex items-center text-lg font-medium">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="mr-2 h-6 w-6"
        >
          <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3" />
        </svg>
        Tsuyanki
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
    <div class="lg:p-8">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
        <LoginForm 
          @auth-success="handleAuthSuccess"
          @switch-to-register="handleSwitchToRegister"
        />
      </div>
    </div>
  </div>
</template>
