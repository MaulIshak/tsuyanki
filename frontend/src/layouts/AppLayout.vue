<script setup>
import { RouterView } from 'vue-router'
import { FolderDot } from 'lucide-vue-next'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
</script>

<template>
  <div class="grid min-h-screen w-full md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]">
    <!-- Desktop Sidebar -->
    <div class="hidden border-r bg-muted/40 md:block">
      <div class="flex h-full max-h-screen flex-col gap-2">
        <div class="flex h-14 items-center border-b px-4 lg:h-[60px] lg:px-6">
          <a href="/" class="flex items-center gap-2 font-semibold">
            <FolderDot class="h-6 w-6 text-indigo-600" />
            <span class="text-indigo-900 dark:text-indigo-100">Tsuyanki</span>
          </a>
        </div>
        <div class="flex-1">
          <AppSidebar />
        </div>
        <div class="mt-auto p-4">
            <!-- Optional Upgrade Card or similar could go here -->
        </div>
      </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex flex-col">
      <AppHeader />
      <main class="flex flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6 bg-muted/20">
        <RouterView />
      </main>
    </div>
  </div>
</template>
