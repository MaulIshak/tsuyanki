<script setup>
import { ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

const emit = defineEmits(['logout'])

const user = ref({})

onMounted(() => {
  // Ambil data user yang tersimpan di localStorage saat login/register
  try {
    const userData = localStorage.getItem('user_data')
    if (userData) {
      user.value = JSON.parse(userData)
    }
  } catch (e) {
    console.error('Gagal memparsing data user', e)
  }
})

const handleLogout = () => {
  // Hapus sesi lokal
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_data')
  
  // Beritahu parent (App.vue) untuk ganti view
  emit('logout')
}
</script>

<template>
  <Card class="w-full max-w-md shadow-lg border-slate-200 dark:border-slate-800">
    <CardHeader class="space-y-1 text-center">
      <CardTitle class="text-2xl font-bold">Dashboard</CardTitle>
      <CardDescription>
        Selamat datang di area member.
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="p-4 rounded-lg bg-slate-100 dark:bg-slate-900">
        <h3 class="font-medium text-sm text-muted-foreground mb-1">Login Sebagai:</h3>
        <p class="text-lg font-semibold">{{ user.name || 'Pengguna' }}</p>
        <p class="text-sm text-slate-500">{{ user.email }}</p>
      </div>
      
      <div class="grid grid-cols-2 gap-4">
        <div class="h-24 rounded-md bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900 flex flex-col items-center justify-center p-2">
            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">0</span>
            <span class="text-xs text-muted-foreground">Decks</span>
        </div>
        <div class="h-24 rounded-md bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900 flex flex-col items-center justify-center p-2">
            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">0</span>
            <span class="text-xs text-muted-foreground">Due Cards</span>
        </div>
      </div>
    </CardContent>
    <CardFooter>
      <Button variant="destructive" class="w-full" @click="handleLogout">
        Logout
      </Button>
    </CardFooter>
  </Card>
</template>