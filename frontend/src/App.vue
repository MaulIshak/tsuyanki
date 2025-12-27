<script setup>
import { ref, onMounted } from 'vue'
import { Toaster } from '@/components/ui/sonner'
import {toast} from 'vue-sonner'
// Import komponen
import LoginForm from './components/LoginForm.vue'
import RegisterForm from './components/RegisterForm.vue'
import Dashboard from './components/Dashboard.vue'

// State management sederhana
// 'login' | 'register' | 'dashboard'
const currentView = ref('login')
const isLoading = ref(true)

// Cek status login saat aplikasi dimuat
onMounted(() => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    currentView.value = 'HomeView'
  }
  isLoading.value = false
})

// Handlers
const showLogin = () => currentView.value = 'login'
const showRegister = () => currentView.value = 'register'

const onAuthSuccess = () => {
  // Reset view ke dashboard setelah login/register sukses
  currentView.value = 'dashboard'
}

const handleLogout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_data')
  currentView.value = 'login'
  toast.success('Berhasil Logout', { description: 'Sampai jumpa lagi!' })
}
</script>

<template>
  <main class="min-h-screen w-full bg-background antialiased">
    
    <Transition name="fade" mode="out-in">
      
      <component 
        :is="currentView === 'login' ? LoginForm : (currentView === 'register' ? RegisterForm : Dashboard)"
        @switch-to-register="showRegister"
        @switch-to-login="showLogin"
        @auth-success="onAuthSuccess"
        @logout="handleLogout"
      />
      
    </Transition>

    <Toaster />
  </main>
</template>

<style>
/* Pastikan tidak ada style global yang mengganggu.
   Biasanya di file style.css / main.css, tapi kita bisa override di sini untuk jaga-jaga.
*/
body, html, #app {
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  overflow-x: hidden; /* Mencegah scroll horizontal jika ada elemen yang offside */
}

/* Sedikit animasi transisi agar perpindahan halus (Opsional) */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>