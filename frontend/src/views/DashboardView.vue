<script setup>
import Dashboard from '@/components/Dashboard.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const handleLogout = () => {
  // Clear logic handled in AppLayout or global store preference later,
  // but for now Dashboard component emits 'logout'.
  // However, with the new layout, the logout button will likely be in the AppHeader.
  // We keep this for compatibility if Dashboard has its own logout.
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_data')
  router.push('/login')
}
</script>

<template>
  <div class="p-6">
    <Dashboard @logout="handleLogout" />
  </div>
</template>
