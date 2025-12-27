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
             <!-- Placeholder Avatar -->
            <AvatarImage src="https://github.com/shadcn.png" alt="@shadcn" />
            <AvatarFallback>CN</AvatarFallback>
          </Avatar>
          <span class="sr-only">Toggle user menu</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuLabel>My Account</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem>Settings</DropdownMenuItem>
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
