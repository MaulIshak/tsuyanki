<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Home, BookOpen, Layers, Upload, Settings } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const route = useRoute()

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: Home },
  { name: 'Study', href: '/study', icon: BookOpen },
  { name: 'Decks', href: '/decks', icon: Layers },
  { name: 'Import', href: '/import', icon: Upload },
  { name: 'Settings', href: '/settings', icon: Settings },
]

// Determine if a link is active
const isActive = (path) => route.path.startsWith(path)
</script>

<template>
  <nav class="grid items-start px-2 text-sm font-medium lg:px-4 gap-1">
    <RouterLink
      v-for="item in navigation"
      :key="item.name"
      :to="item.href"
      :class="cn(
        'flex items-center gap-3 rounded-lg px-3 py-2 transition-all hover:text-primary',
        isActive(item.href) 
          ? 'bg-muted text-primary' 
          : 'text-muted-foreground'
      )"
    >
      <component :is="item.icon" class="h-4 w-4" />
      {{ item.name }}
    </RouterLink>
  </nav>
</template>
