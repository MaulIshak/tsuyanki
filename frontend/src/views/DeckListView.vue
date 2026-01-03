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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
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

// Study Dialog State
const studyDeck = ref(null)
const studyLimit = ref(20)
const isStudyOpen = ref(false)

const openStudyDialog = (deck) => {
    studyDeck.value = deck
    studyLimit.value = 20
    isStudyOpen.value = true
}

const startStudy = () => {
    if (!studyDeck.value) return
    isStudyOpen.value = false
    router.push({
        path: '/study',
        query: {
            deck_id: studyDeck.value.id,
            limit: studyLimit.value
        }
    })
}

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
  <div class="space-y-4 sm:space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">My Decks</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Manage your flashcard collections.</p>
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
        class="group bg-white/90 dark:bg-slate-900/40 backdrop-blur-sm border-slate-200/60 dark:border-slate-800/60 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all duration-300 cursor-pointer flex flex-col hover:shadow-lg hover:-translate-y-1"
        @click="navigateToDeck(deck.id)"
      >
        <CardHeader class="flex flex-row items-start justify-between space-y-0 pb-2">
           <div class="space-y-1 pr-4">
              <CardTitle class="text-lg font-bold line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ deck.title }}</CardTitle>
              <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 min-h-[2.5em] leading-relaxed">
                {{ deck.description || 'No description provided.' }}
              </p>
           </div>
           <DropdownMenu>
              <DropdownMenuTrigger as-child>
                 <Button variant="ghost" size="icon" class="-mt-1 -mr-2 h-8 w-8 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" @click.stop>
                    <MoreHorizontal class="w-4 h-4" />
                 </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-48">
                 <DropdownMenuItem @click.stop="navigateToDeck(deck.id)">Edit Deck</DropdownMenuItem>
                 <DropdownMenuItem @click.stop="openStudyDialog(deck)">Study Now</DropdownMenuItem>
                 <DropdownMenuItem @click.stop="openDeleteDialog(deck)" class="text-red-600 focus:text-red-600">Delete</DropdownMenuItem>
              </DropdownMenuContent>
           </DropdownMenu>
        </CardHeader>
        
        <CardContent class="flex-1 p-4 pt-2">
           <div class="flex items-center justify-between mt-auto">
              <Badge variant="secondary" class="font-normal bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 hover:bg-indigo-100 transition-colors">
                 <Layers class="w-3 h-3 mr-1" />
                 {{ deck.notes_count || 0 }} cards
              </Badge>
           </div>
        </CardContent>

        <CardFooter class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/20 p-4 text-xs text-slate-500 dark:text-slate-400 flex justify-between items-center">
             <div class="flex items-center gap-1.5 opacity-80">
                <Clock class="w-3 h-3" />
                <span>Last: Never</span>
             </div>
             <Button size="sm" class="h-7 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:hover:bg-indigo-800 dark:text-indigo-200 border-0 transition-colors shadow-sm" @click.stop="openStudyDialog(deck)">
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

    <Dialog :open="isStudyOpen" @update:open="isStudyOpen = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Study "{{ studyDeck?.title }}"</DialogTitle>
          <DialogDescription>
            How many cards would you like to review in this session?
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label for="limit" class="text-right">
              Cards
            </Label>
            <Input
              id="limit"
              v-model="studyLimit"
              type="number"
              min="1"
              :max="studyDeck?.notes_count || 100"
              class="col-span-3"
            />
            <p class="col-span-4 text-xs text-slate-500 text-right" v-if="studyDeck">
                Max available: {{ studyDeck.notes_count }}
            </p>
          </div>
        </div>
        <DialogFooter>
          <Button @click="startStudy">Start Session</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>
