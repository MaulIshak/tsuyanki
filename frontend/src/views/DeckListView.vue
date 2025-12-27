<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch, useDebounceFn } from '@vueuse/core'
import { Search, Plus, MoreHorizontal, BookOpen, Clock, Layers } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationContent,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { toast } from 'vue-sonner'


const router = useRouter()
const route = useRoute()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// State
const decks = ref([])
const totalDecks = ref(0)
const currentPage = ref(1)
const searchQuery = ref('')
const isLoading = ref(true)

// Fetch Decks
const fetchDecks = async () => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  const page = currentPage.value
  const query = searchQuery.value

  const url = new URL(`${API_BASE_URL}/decks`)
  url.searchParams.append('page', page)
  url.searchParams.append('limit', 6)
  if (query) url.searchParams.append('search', query)

  try {
    const { data } = await useFetch(url.toString(), {
      headers: { Authorization: `Bearer ${token}` }
    }).json()

    if (data.value) {
      decks.value = data.value.data
      totalDecks.value = data.value.meta.total
      currentPage.value = data.value.meta.current_page
    }
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Debounced Search
const onSearch = useDebounceFn(() => {
  currentPage.value = 1
  fetchDecks()
}, 500)

watch(searchQuery, onSearch)

// Initial Load
onMounted(() => {
  fetchDecks()
})

const deckToDelete = ref(null)
const isDeleteOpen = ref(false)
const isDeleting = ref(false)

const openDeleteDialog = (deck) => {
    deckToDelete.value = deck
    isDeleteOpen.value = true
}

const executeDelete = async () => {
    if (!deckToDelete.value) return
    isDeleting.value = true
    const token = localStorage.getItem('auth_token')
    
    try {
        const { error } = await useFetch(`${API_BASE_URL}/decks/${deckToDelete.value.id}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` }
        })
        
        if (error.value) throw new Error('Delete failed')
        
        toast.success('Deck deleted')
        decks.value = decks.value.filter(d => d.id !== deckToDelete.value.id)
        totalDecks.value--
    } catch (e) {
         toast.error('Failed to delete deck')
    } finally {
        isDeleting.value = false
        isDeleteOpen.value = false
        deckToDelete.value = null
    }
}

const navigateToDeck = (id) => {
    router.push(`/decks/${id}`)
}

</script>

<template>
  <div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">My Decks</h1>
        <p class="text-slate-500 dark:text-slate-400">Manage your flashcard collections.</p>
      </div>
      <Button @click="router.push('/decks/create')" class="gap-2">
        <Plus class="w-4 h-4" /> Create Deck
      </Button>
    </div>

    <!-- Search Tooltips/Filters -->
    <div class="relative w-full max-w-md">
      <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
      <Input
        v-model="searchQuery"
        type="search"
        placeholder="Search decks..."
        class="pl-8 bg-white dark:bg-slate-900"
      />
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="i in 6" :key="i" class="overflow-hidden">
        <CardHeader class="pb-2">
          <Skeleton class="h-6 w-3/4 mb-2" />
          <Skeleton class="h-4 w-1/2" />
        </CardHeader>
        <CardContent>
          <Skeleton class="h-4 w-full mb-2" />
          <Skeleton class="h-4 w-2/3" />
        </CardContent>
      </Card>
    </div>

    <!-- Empty State -->
    <div v-else-if="decks.length === 0" class="text-center py-20 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-dashed border-slate-300 dark:border-slate-800">
        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <Layers class="w-8 h-8 text-slate-400" />
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">No decks found</h3>
        <p class="text-slate-500 dark:text-slate-400 mb-6 max-w-sm mx-auto">You haven't created any decks yet, or your search returned no results.</p>
        <Button @click="router.push('/decks/create')" variant="outline">Create your first deck</Button>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card 
        v-for="deck in decks" 
        :key="deck.id" 
        class="group hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors cursor-pointer flex flex-col"
        @click="navigateToDeck(deck.id)"
      >
        <CardHeader class="flex flex-row items-start justify-between space-y-0 ">
           <div class="space-y-1 pr-4">
              <CardTitle class="text-base line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ deck.title }}</CardTitle>
              <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 min-h-[2.5em]">
                {{ deck.description || 'No description provided.' }}
              </p>
           </div>
           <DropdownMenu>
              <DropdownMenuTrigger as-child>
                 <Button variant="ghost" size="icon" class="-mt-1 -mr-2 h-6 w-6 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" @click.stop>
                    <MoreHorizontal class="w-4 h-4" />
                 </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                 <DropdownMenuItem @click.stop="navigateToDeck(deck.id)">Edit Deck</DropdownMenuItem>
                 <DropdownMenuItem @click.stop="router.push(`/study?deck_id=${deck.id}`)">Study Now</DropdownMenuItem>
                 <DropdownMenuItem @click.stop="openDeleteDialog(deck)" class="text-red-600 focus:text-red-600">Delete</DropdownMenuItem>
              </DropdownMenuContent>
           </DropdownMenu>
        </CardHeader>
        
        <CardContent class="flex-1 p-4 pt-2">
           <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                 <Layers class="w-4 h-4" />
                 <span>{{ deck.notes_count || 0 }} notes</span>
              </div>
           </div>
        </CardContent>

        <CardFooter class="border-t bg-slate-50/50 dark:bg-slate-900/30  text-xs text-slate-500 dark:text-slate-400 flex justify-between items-center">
             <div class="flex items-center gap-1">
                <Clock class="w-3 h-3" />
                <span>Last studied: Never</span>
             </div>
             <Button size="sm" variant="ghost" class="h-6 text-xs hover:bg-white dark:hover:bg-slate-800" @click.stop="router.push(`/study?deck_id=${deck.id}`)">
                Study
             </Button>
        </CardFooter>
      </Card>
    </div>
    
    <!-- Pagination -->
    <div v-if="totalDecks > 6" class="flex justify-center pt-8 pb-4">
        <Pagination v-slot="{ page }" :total="totalDecks" :sibling-count="1" show-edges :default-page="1" :items-per-page="6" @update:page="(val) => { currentPage = val; fetchDecks() }">
          <PaginationContent v-slot="{ items }" class="flex items-center gap-1">
            <PaginationFirst />
            <PaginationPrevious />
            <template v-for="(item, index) in items">
              <PaginationItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                <Button class="w-10 h-10 p-0" :variant="item.value === page ? 'default' : 'outline'">
                  {{ item.value }}
                </Button>
              </PaginationItem>
              <PaginationEllipsis v-else :key="item.type" :index="index" />
            </template>
            <PaginationNext />
            <PaginationLast />
          </PaginationContent>
        </Pagination>
    </div>



    <AlertDialog :open="isDeleteOpen" @update:open="isDeleteOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the deck
            <span class="font-bold text-slate-900 dark:text-slate-100" v-if="deckToDelete">"{{ deckToDelete.title }}"</span>
            and all its cards.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" :disabled="isDeleting" class="bg-red-600 hover:bg-red-700 focus:ring-red-600 text-white">
            {{ isDeleting ? 'Deleting...' : 'Delete' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>
