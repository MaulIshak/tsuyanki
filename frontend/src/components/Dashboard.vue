<script setup>
import { Button } from '@/components/ui/button'

const emit = defineEmits(['logout'])
const user = JSON.parse(localStorage.getItem('user_data') || '{}')
</script>

<template>
  <div class="flex flex-col items-center justify-center h-screen space-y-4">
    <h1 class="text-3xl font-bold">Halo, {{ user.name || 'User' }}!</h1>
    <p class="text-muted-foreground">Selamat datang di Dashboard Tsuyanki.</p>
    <Button variant="destructive" @click="$emit('logout')">Logout</Button>
  </div>
</template>