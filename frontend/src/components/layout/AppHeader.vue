<script setup>
import { ref } from 'vue'
import { CircleUser, Menu, FolderDot, Loader2, LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import AppSidebar from './AppSidebar.vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { inject } from 'vue'
import { PanelLeftClose, PanelLeftOpen, Sun, Moon } from 'lucide-vue-next'
import { useDark, useToggle } from '@vueuse/core'

const toggleSidebar = inject('toggleSidebar')
const isSidebarCollapsed = inject('isSidebarCollapsed')

const isDark = useDark()
const toggleDark = useToggle(isDark)

const router = useRouter()
const isLoading = ref(false)
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// User Data State
import { onMounted, computed } from 'vue'
import { useFetch } from '@vueuse/core'

const userData = ref(null)

const fetchUser = async () => {
    const token = localStorage.getItem('auth_token')
    if (!token) return

    try {
        const { data } = await useFetch(`${API_BASE_URL}/auth/me`, {
             headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
            userData.value = data.value
            // Cache simpler user info if needed
            localStorage.setItem('user_data', JSON.stringify(data.value))
        }
    } catch (e) {
        console.error("Failed to fetch user", e)
    }
}

const userInitials = computed(() => {
    const name = userData.value?.name || 'User'
    const parts = name.split(' ')
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase()
    }
    return name.slice(0, 2).toUpperCase()
})

onMounted(() => {
    // Try to load from local storage first for speed
    const cached = localStorage.getItem('user_data')
    if (cached) {
        try {
            userData.value = JSON.parse(cached)
        } catch(e) {}
    }
    fetchUser()
})

const handleLogout = async (event) => {
  event?.preventDefault() // Prevent dropdown closing immediately if needed, though usually fine.
  
  if (isLoading.value) return

  isLoading.value = true
  const token = localStorage.getItem('auth_token')

  try {
    if (token) {
       await fetch(`${API_BASE_URL}/auth/logout`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
      })
    }
  } catch (error) {
    console.error("Logout failed", error)
    // We proceed to clear local state anyway to prevent being stuck
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    toast.success('Berhasil Logout', { description: 'Sampai jumpa lagi!' })
    isLoading.value = false
    router.push('/login')
  }
}
</script>

<template>
  <header class="flex h-14 items-center gap-4 border-b bg-white/80 backdrop-blur-md dark:bg-slate-950/80 dark:border-slate-800 px-4 lg:h-[60px] lg:px-6 sticky top-0 z-50">
    <!-- Mobile Sidebar Trigger -->
    <Sheet>
      <SheetTrigger as-child>
        <Button
          variant="outline"
          size="icon"
          class="shrink-0 md:hidden"
        >
          <Menu class="h-5 w-5" />
          <span class="sr-only">Toggle navigation menu</span>
        </Button>
      </SheetTrigger>
      <SheetContent side="left" class="flex flex-col">
        <nav class="grid gap-2 text-lg font-medium">
          <a
            href="#"
            class="flex items-center gap-2 text-lg font-semibold mb-6 pt-6 pl-6"
          >
            <img src="/logo.png" alt="Tsuyanki" class="h-8 w-auto dark:brightness-0 dark:invert" />
            <span class="text-slate-900 dark:text-slate-100">Tsuyanki</span>
          </a>
          <AppSidebar />
        </nav>
      </SheetContent>
    </Sheet>

    <!-- Desktop Sidebar Trigger -->
    <Button 
      variant="ghost" 
      size="icon" 
      class="hidden md:flex text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
      @click="toggleSidebar"
    >
        <component :is="isSidebarCollapsed ?  PanelLeftOpen : PanelLeftClose" class="h-5 w-5" />
    </Button>

    <!-- Desktop Logo / Spacer (if needed) -->
    <div class="w-full flex-1">
      <form v-if="false"> 
        <!-- Optional Search Bar placeholder -->
        <div class="relative">
          
        </div>
      </form>
    </div>

    <!-- Theme Toggle -->
    <Button 
      variant="ghost" 
      size="icon" 
      class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
      @click="toggleDark()"
    >
        <component :is="isDark ? Sun : Moon" class="h-5 w-5" />
    </Button>

    <!-- User Menu -->
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="ghost" size="icon" class="rounded-full">
          <Avatar>
             <!-- No image for now, purely initials based as requested -->
            <AvatarFallback class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 font-bold">
                {{ userInitials }}
            </AvatarFallback>
          </Avatar>
          <span class="sr-only">Toggle user menu</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" class="w-56">
        <DropdownMenuLabel class="font-normal">
          <div class="flex flex-col space-y-1">
            <p class="text-sm font-medium leading-none">{{ userData?.name || 'User' }}</p>
            <p class="text-xs leading-none text-muted-foreground">{{ userData?.email || 'user@example.com' }}</p>
          </div>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="router.push('/settings')">Settings</DropdownMenuItem>
        <DropdownMenuItem>Support</DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="handleLogout" class="text-red-600 focus:text-red-600 cursor-pointer">
          <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
          <LogOut v-else class="mr-2 h-4 w-4" />
          Logout
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </header>
</template>
