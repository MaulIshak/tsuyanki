<script setup>
import { RouterView } from 'vue-router'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import BackgroundDecorations from '@/components/layout/BackgroundDecorations.vue'
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
    class="fixed inset-0 grid w-full overflow-hidden transition-all duration-500 ease-in-out bg-background"
    :class="isSidebarCollapsed ? 'md:grid-cols-[70px_1fr] lg:grid-cols-[70px_1fr]' : 'md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]'"
  >
    <!-- Background Decorations -->
    <BackgroundDecorations />

    <!-- Desktop Sidebar - Glassmorphism update -->
    <div class="hidden border-r border-sidebar-border bg-sidebar/80 backdrop-blur-md md:block overflow-hidden transition-all duration-500 z-20 shadow-sm relative">
      <div class="flex h-full max-h-screen flex-col gap-2">
        <div class="flex h-14 items-center border-b border-sidebar-border px-4 lg:h-[60px] lg:px-6 shrink-0 transition-all duration-300" :class="isSidebarCollapsed ? 'justify-center px-2' : ''">
            <img src="/logo.png" alt="Tsuyanki" class="h-8 w-auto object-contain shrink-0 transition-all duration-300 transform hover:scale-105" />
            <span 
                class="font-semibold text-lg text-sidebar-foreground tracking-tight transition-all duration-500 ml-3 whitespace-nowrap"
                :class="isSidebarCollapsed ? 'w-0 opacity-0 overflow-hidden ml-0' : 'w-auto opacity-100'"
            >
                Tsuyanki
            </span>
        </div>
        <div class="flex-1 overflow-y-auto py-2">
          <AppSidebar :collapsed="isSidebarCollapsed" />
        </div>
        <div class="mt-auto p-4 shrink-0 border-t border-sidebar-border" v-if="!isSidebarCollapsed">
             <!-- Footer content removed as requested -->
        </div>
      </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex flex-col h-full overflow-hidden relative z-10 transition-all duration-500">
      <AppHeader class="bg-background/60 backdrop-blur-md border-b border-border/40 sticky top-0 z-30" />
      <main class="flex-1 overflow-y-auto p-4 lg:p-6 scroll-smooth">
        <div class="max-w-7xl mx-auto w-full ease-in-out duration-500">
          <RouterView v-slot="{ Component }">
             <transition name="fade" mode="out-in">
                <component :is="Component" />
             </transition>
          </RouterView>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
