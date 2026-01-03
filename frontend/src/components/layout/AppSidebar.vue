<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Home, BookOpen, Layers, Upload, Settings, Info } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()
// ...

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
  <TooltipProvider>
    <nav class="grid items-start px-2 text-sm font-medium lg:px-4 gap-1">
      <template v-for="item in navigation" :key="item.name">
        <Tooltip v-if="collapsed" :delay-duration="0">
          <TooltipTrigger as-child>
            <RouterLink
              :to="item.href"
              :class="cn(
                'flex items-center justify-center rounded-lg py-2 transition-all duration-200',
                isActive(item.href) 
                  ? 'bg-indigo-600 text-white shadow-md' 
                  : 'text-slate-400 hover:text-indigo-100 hover:bg-slate-800'
              )"
            >
              <component :is="item.icon" class="h-5 w-5" />
              <span class="sr-only">{{ item.name }}</span>
            </RouterLink>
          </TooltipTrigger>
          <TooltipContent side="right" class="bg-slate-800 text-slate-200 border-slate-700 ml-2">
            {{ item.name }}
          </TooltipContent>
        </Tooltip>

        <RouterLink
          v-else
          :to="item.href"
          :class="cn(
            'flex items-center gap-3 rounded-lg px-3 py-2 transition-all duration-200',
            isActive(item.href) 
              ? 'bg-indigo-600 text-white shadow-md' 
              : 'text-slate-400 hover:text-indigo-100 hover:bg-slate-800'
          )"
        >
          <component :is="item.icon" :class="cn('h-4 w-4', isActive(item.href) ? 'text-white' : 'text-slate-400 group-hover:text-indigo-100')" />
          <span>{{ item.name }}</span>
        </RouterLink>
      </template>
    </nav>
  </TooltipProvider>
</template>
