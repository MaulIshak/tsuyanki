<script setup>
import { RouterView } from 'vue-router'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import { ref, provide } from 'vue'

const isSidebarCollapsed = ref(false)
const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value
}

provide('toggleSidebar', toggleSidebar)
provide('isSidebarCollapsed', isSidebarCollapsed)
</script>

<template>
  <div 
    class="grid h-screen w-full overflow-hidden transition-all duration-300 ease-in-out"
    :class="isSidebarCollapsed ? 'md:grid-cols-[70px_1fr] lg:grid-cols-[70px_1fr]' : 'md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]'"
  >
    <!-- Desktop Sidebar -->
    <div class="hidden border-r bg-slate-900 md:block overflow-hidden transition-all duration-300">
      <div class="flex h-full max-h-screen flex-col gap-2">
        <div class="flex h-14 items-center border-b border-slate-800 px-4 lg:h-[60px] lg:px-6 shrink-0 transition-all duration-300" :class="isSidebarCollapsed ? 'justify-center px-2' : ''">
            <img src="/logo.png" alt="Tsuyanki" class="h-8 w-auto object-contain shrink-0 brightness-0 invert transition-all duration-300" />
            <span 
                class="font-semibold text-lg text-white tracking-tight transition-all duration-300 ml-2 whitespace-nowrap"
                :class="isSidebarCollapsed ? 'w-0 opacity-0 overflow-hidden ml-0' : 'w-auto opacity-100'"
            >
                Tsuyanki
            </span>
        </div>
        <div class="flex-1 overflow-y-auto py-2">
          <AppSidebar :collapsed="isSidebarCollapsed" />
        </div>
        <div class="mt-auto p-4 shrink-0 border-t border-slate-800" v-if="!isSidebarCollapsed">
             <!-- Footer content removed as requested -->
        </div>
      </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex flex-col h-full overflow-hidden">
      <AppHeader />
      <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-slate-50 dark:bg-slate-950/50">
        <RouterView />
      </main>
    </div>
  </div>
</template>
