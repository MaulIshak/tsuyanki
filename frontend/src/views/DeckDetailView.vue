<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch, useDebounceFn } from '@vueuse/core'
import { ArrowLeft, Plus, MoreHorizontal, Settings, FileText, Trash2, Pencil, Loader2, Search, Filter, SortAsc, SortDesc, Tag as TagIcon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuCheckboxItem,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
} from '@/components/ui/dropdown-menu'
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
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { toast } from 'vue-sonner'
import { deserializeContent } from '@/lib/mediaUtils'

const router = useRouter()
const route = useRoute()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
const deckId = route.params.id

// State
const deck = ref(null)
const notes = ref([])
const totalNotes = ref(0)
const currentPage = ref(1)
const isLoading = ref(true)

// Filter/Sort State
const searchQuery = ref('')
const sortOrder = ref('desc') // desc or asc
const sortBy = ref('created_at') // created_at or updated_at
const filterTag = ref('all') // 'all' or specific tag name
const availableTags = ref([])

// Fetch Deck Details
const fetchDeck = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        const { data } = await useFetch(`${API_BASE_URL}/decks/${deckId}`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        if (data.value) deck.value = data.value
    } catch (e) {
        toast.error('Failed to load deck')
    }
}

// Fetch Available Tags
const fetchTags = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        // Ideally backend should provide tags specific to this deck or all tags
        // For now fetching all tags
        const { data } = await useFetch(`${API_BASE_URL}/tags?deck_id=${deckId}&limit=100`, {
             headers: { Authorization: `Bearer ${token}` }
        }).json()
        if (data.value) availableTags.value = data.value.data
    } catch (e) {
        console.error('Failed to load tags')
    }
}

// Fetch Notes in Deck
const fetchNotes = async () => {
    isLoading.value = true
    const token = localStorage.getItem('auth_token')
    try {
        const url = new URL(`${API_BASE_URL}/decks/${deckId}/notes`)
        url.searchParams.append('page', currentPage.value)
        url.searchParams.append('limit', 20)
        
        if (searchQuery.value) url.searchParams.append('search', searchQuery.value)
        if (filterTag.value && filterTag.value !== 'all') url.searchParams.append('tag', filterTag.value)
        
        url.searchParams.append('sort', sortBy.value)
        url.searchParams.append('order', sortOrder.value)

        const { data } = await useFetch(url.toString(), {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
             notes.value = data.value.data
             totalNotes.value = data.value.meta.total
             currentPage.value = data.value.meta.current_page
        }
    } catch (e) {
        toast.error('Failed to load cards')
    } finally {
        isLoading.value = false
    }
}

// Debounced Search
const onSearch = useDebounceFn(() => {
    currentPage.value = 1
    fetchNotes()
}, 500)

const onFilterChange = () => {
    currentPage.value = 1
    fetchNotes()
}

// ... (Delete Note logic remains same) ...
const noteToDelete = ref(null)
const isNoteDeleteOpen = ref(false)
const isNoteDeleting = ref(false)

const confirmDeleteNote = (note) => {
    noteToDelete.value = note
    isNoteDeleteOpen.value = true
}

const executeDeleteNote = async () => {
    if (!noteToDelete.value) return
    isNoteDeleting.value = true
    const token = localStorage.getItem('auth_token')
    const noteId = noteToDelete.value.id

    try {
        await useFetch(`${API_BASE_URL}/notes/${noteId}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` }
        })
        notes.value = notes.value.filter(n => n.id !== noteId)
        totalNotes.value--
        toast.success('Note deleted')
    } catch (e) {
        toast.error('Failed to delete note')
    } finally {
        isNoteDeleting.value = false
        isNoteDeleteOpen.value = false
        noteToDelete.value = null
    }
}

const navigateToEdit = (noteId) => {
    router.push(`/cards/${noteId}/edit`)
}

const navigateToAdd = () => {
    router.push(`/decks/${deckId}/add`)
}

// ... (Deck Edit/Delete Logic remains same) ...
const editForm = ref({
    title: '',
    description: ''
})
const isUpdating = ref(false)
const isDeleting = ref(false)

const updateDeck = async () => {
    isUpdating.value = true
    const token = localStorage.getItem('auth_token')
    
    try {
        const { error } = await useFetch(`${API_BASE_URL}/decks/${deckId}`, {
            method: 'PUT',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(editForm.value)
        }).json()

        if (error.value) throw new Error('Update failed')
        
        toast.success('Deck updated')
        if (deck.value) {
            deck.value.title = editForm.value.title
            deck.value.description = editForm.value.description
        }
    } catch (e) {
        toast.error('Failed to update deck')
    } finally {
        isUpdating.value = false
    }
}

const isSyncing = ref(false)
const syncDeck = async () => {
    isSyncing.value = true
    const token = localStorage.getItem('auth_token')
    try {
        const { data } = await useFetch(`${API_BASE_URL}/decks/${deckId}/sync`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
            toast.success(data.value.message)
            fetchNotes() // Refresh counts
        }
    } catch (e) {
        toast.error('Sync failed')
    } finally {
        isSyncing.value = false
    }
}

const deleteDeck = async () => {
    if (!confirm('Are you absolutely sure? This will delete all cards in this deck.')) return
    
    isDeleting.value = true
    const token = localStorage.getItem('auth_token')

    try {
        const { error } = await useFetch(`${API_BASE_URL}/decks/${deckId}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` }
        })
        
        if (error.value) throw new Error('Delete failed')
        
        toast.success('Deck deleted')
        router.push('/decks')
    } catch (e) {
         toast.error('Failed to delete deck')
         isDeleting.value = false
    }
}

onMounted(async () => {
    await Promise.all([fetchDeck(), fetchNotes(), fetchTags()])
})

// ... (Rest of logic: watch, getNoteFront, getNoteBack) ...
watch(deck, (newDeck) => {
    if (newDeck) {
        editForm.value = { 
            title: newDeck.title, 
            description: newDeck.description 
        }
    }
}, { immediate: true })

const getNoteFront = (note) => {
    if (!note.fields) return { label: 'Front', content: 'N/A' }
    const excludeKeys = ['pos', 'part of speech', 'id', 'audio', 'sound', 'image', 'picture', 'note', 'notes']
    const priorityKeys = ['expression', 'vocabulary', 'vocab', 'word', 'kanji', 'term', 'front', 'question', 'kana']
    const keys = Object.keys(note.fields)
    const lowerMap = keys.reduce((acc, k) => { acc[k.toLowerCase()] = k; return acc }, {})
    for (const key of priorityKeys) { if (lowerMap[key]) return { label: lowerMap[key], content: deserializeContent(note.fields[lowerMap[key]]) } }
    for (const key of keys) { if (!excludeKeys.includes(key.toLowerCase())) return { label: key, content: deserializeContent(note.fields[key]) } }
    const firstKey = keys[0]; return { label: firstKey || 'Front', content: deserializeContent(note.fields[firstKey] || 'N/A') }
}

const getNoteBack = (note) => {
    if (!note.fields) return { label: 'Back', content: '' }
    const excludeKeys = ['audio', 'sound', 'image', 'picture']
    const priorityKeys = ['meaning', 'definition', 'english', 'reading', 'back', 'answer', 'glossary']
    const keys = Object.keys(note.fields)
    const lowerMap = keys.reduce((acc, k) => { acc[k.toLowerCase()] = k; return acc }, {})
    for (const key of priorityKeys) { if (lowerMap[key]) return { label: lowerMap[key], content: deserializeContent(note.fields[lowerMap[key]]) } }
    const validKeys = keys.filter(k => !excludeKeys.includes(k.toLowerCase()))
    if (validKeys.length > 1) return { label: validKeys[1], content: deserializeContent(note.fields[validKeys[1]]) }
    return { label: 'Back', content: '' }
}
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6">
    
    <!-- Back & Header -->
    <div class="space-y-4">
        <Button variant="ghost" class="pl-0 gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100" @click="router.push('/decks')">
            <ArrowLeft class="w-4 h-4" /> Back to Decks
        </Button>

        <div class="flex justify-between items-start" v-if="deck">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">{{ deck.title }}</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-3xl">{{ deck.description }}</p>
            </div>
             <Button class="gap-2" @click="navigateToAdd">
                <Plus class="w-4 h-4" /> Add Card
            </Button>
        </div>
        <div v-else-if="isLoading">
             <Skeleton class="h-10 w-1/3 mb-2" />
             <Skeleton class="h-4 w-1/2" />
        </div>
    </div>

    <!-- Content -->
    <Tabs defaultValue="cards" class="w-full">
        <TabsList class="mb-4">
            <TabsTrigger value="cards" class="gap-2">
                <FileText class="w-4 h-4" /> Cards
            </TabsTrigger>
            <TabsTrigger value="settings" class="gap-2">
                <Settings class="w-4 h-4" /> Settings
            </TabsTrigger>
        </TabsList>

        <TabsContent value="cards" class="space-y-4">
            
            <!-- Controls (Search, Filter, Sort) -->
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-lg border shadow-sm">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
                    <Input 
                        placeholder="Search cards..." 
                        class="pl-9" 
                        v-model="searchQuery"
                        @input="onSearch"
                    />
                </div>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    <!-- Filter Tag -->
                    <Select v-model="filterTag" @update:model-value="onFilterChange">
                        <SelectTrigger class="w-[150px]">
                            <div class="flex items-center gap-2">
                                <Filter class="w-3.5 h-3.5" />
                                <span class="truncate">{{ filterTag === 'all' ? 'All Tags' : filterTag }}</span>
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Tags</SelectItem>
                            <SelectItem v-for="tag in availableTags" :key="tag.id" :value="tag.name">
                                {{ tag.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Sort -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="gap-2">
                                <SortAsc v-if="sortOrder === 'asc'" class="w-4 h-4" />
                                <SortDesc v-else class="w-4 h-4" />
                                Sort
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel>Sort By</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuRadioGroup v-model="sortBy" @update:model-value="onFilterChange">
                                <DropdownMenuRadioItem value="created_at">Created Date</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="updated_at">Updated Date</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                            <DropdownMenuSeparator />
                            <DropdownMenuLabel>Order</DropdownMenuLabel>
                            <DropdownMenuRadioGroup v-model="sortOrder" @update:model-value="onFilterChange">
                                <DropdownMenuRadioItem value="desc">Newest First</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="asc">Oldest First</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
            <div class="space-y-4 md:hidden">
                <div v-if="isLoading" class="space-y-4">
                     <Skeleton v-for="i in 3" :key="i" class="h-32 w-full rounded-lg" />
                </div>
                <div v-else-if="notes.length === 0" class="text-center py-12 text-slate-500 border-2 border-dashed rounded-lg">
                    No cards found. Start by adding one!
                </div>
                <div v-else v-for="note in notes" :key="note.id" class="rounded-lg border bg-white dark:bg-slate-900 p-4 space-y-3 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="flex flex-col gap-1">
                            <Badge variant="secondary" class="text-xs shrink-0 self-start">{{ note.note_type?.name || 'Card' }}</Badge>
                             <div v-if="note.tags && note.tags.length > 0" class="flex flex-wrap gap-1">
                                <Badge v-for="tag in note.tags" :key="tag.id" variant="outline" class="text-[10px] px-1 py-0 h-4">
                                    {{ tag.name }}
                                </Badge>
                             </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0 -mr-2 -mt-2 text-slate-400">
                                    <span class="sr-only">Open menu</span>
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="navigateToEdit(note.id)">
                                    <Pencil class="mr-2 h-4 w-4" /> Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="confirmDeleteNote(note)" class="text-red-600 focus:text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    
                    <div class="grid gap-2">
                        <div v-if="getNoteFront(note).content">
                            <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">{{ getNoteFront(note).label }}</div>
                            <div class="font-medium text-lg leading-snug break-words" v-html="getNoteFront(note).content"></div>
                        </div>
                        <div v-if="getNoteBack(note).content" class="border-t pt-2 mt-1">
                            <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">{{ getNoteBack(note).label }}</div>
                            <div class="text-slate-600 dark:text-slate-300 leading-snug break-words" v-html="getNoteBack(note).content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Table (Hidden on mobile, visible md+) -->
            <div class="hidden md:block rounded-md border bg-white dark:bg-slate-900">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[30%]">Front</TableHead>
                            <TableHead class="w-[30%]">Back</TableHead>
                            <TableHead class="w-[15%]">Type</TableHead>
                            <TableHead class="w-[15%]">Tags</TableHead>
                            <TableHead class="text-right w-[10%]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="isLoading" v-for="i in 5" :key="i">
                             <TableCell><Skeleton class="h-4 w-20" /></TableCell>
                             <TableCell><Skeleton class="h-4 w-32" /></TableCell>
                             <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                             <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                             <TableCell><Skeleton class="h-8 w-8 ml-auto" /></TableCell>
                        </TableRow>
                        
                        <TableRow v-else-if="notes.length === 0">
                            <TableCell colspan="5" class="h-24 text-center">
                                No cards found. Start by adding one!
                            </TableCell>
                        </TableRow>

                        <TableRow v-else v-for="note in notes" :key="note.id">
                            <TableCell class="font-medium truncate max-w-[250px] align-top">
                                <span class="text-xs text-slate-400 block mb-0.5">{{ getNoteFront(note).label }}</span>
                                <span v-html="getNoteFront(note).content"></span>
                            </TableCell>
                            <TableCell class="truncate max-w-[250px] align-top">
                                <span class="text-xs text-slate-400 block mb-0.5">{{ getNoteBack(note).label }}</span>
                                <span v-html="getNoteBack(note).content"></span>
                            </TableCell>
                            <TableCell class="align-top">
                                <Badge variant="secondary" class="text-xs whitespace-nowrap">
                                    {{ note.note_type?.name || 'Card' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="align-top">
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-for="tag in note.tags" :key="tag.id" variant="outline" class="text-[10px] px-1 h-5 whitespace-nowrap">
                                        {{ tag.name }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell class="text-right align-top">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" class="h-8 w-8 p-0">
                                            <span class="sr-only">Open menu</span>
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="navigateToEdit(note.id)">
                                            <Pencil class="mr-2 h-4 w-4" /> Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="confirmDeleteNote(note)" class="text-red-600 focus:text-red-600">
                                            <Trash2 class="mr-2 h-4 w-4" /> Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="totalNotes > 20" class="py-4">
                 <Pagination v-slot="{ page }" :total="totalNotes" :sibling-count="1" show-edges :default-page="1" :items-per-page="20" @update:page="(val) => { currentPage = val; fetchNotes() }">
                  <PaginationContent v-slot="{ items }" class="flex items-center gap-1 justify-center">
                    <PaginationFirst />
                    <PaginationPrevious />
                    <template v-for="(item, index) in items">
                      <PaginationItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                        <Button class="w-9 h-9 p-0" :variant="item.value === page ? 'default' : 'outline'">
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
            
        </TabsContent>

        <TabsContent value="settings">
             <div class="rounded-lg border bg-white dark:bg-slate-900 p-6 space-y-6">
                 <div>
                     <h3 class="text-lg font-medium">Deck Settings</h3>
                     <p class="text-sm text-slate-500">Manage your deck's preferences.</p>
                 </div>
                 
                 <div v-if="deck" class="grid gap-4 max-w-sm">
                     <div class="space-y-2">
                        <label class="text-sm font-medium">Deck Title</label>
                        <Input v-model="editForm.title" />
                     </div>
                      <div class="space-y-2">
                        <label class="text-sm font-medium">Description</label>
                        <Textarea v-model="editForm.description" class="resize-none h-32" />
                     </div>
                     <Button @click="updateDeck" :disabled="isUpdating">
                        <Loader2 v-if="isUpdating" class="w-4 h-4 mr-2 animate-spin" />
                        Save Changes
                     </Button>
                 </div>
                 <div v-else class="space-y-4 max-w-sm">
                     <Skeleton class="h-10 w-full" />
                     <Skeleton class="h-32 w-full" />
                 </div>
                 
                 <div class="pt-6 border-t space-y-4">
                      <h3 class="text-lg font-medium">Maintenance</h3>
                      <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                          <div>
                              <div class="font-medium text-slate-900 dark:text-slate-100">Sync Cards</div>
                              <div class="text-sm text-slate-500">Fix missing cards if deck seems empty but has notes.</div>
                          </div>
                          <Button variant="outline" @click="syncDeck" :disabled="isSyncing">
                              <Loader2 v-if="isSyncing" class="w-4 h-4 mr-2 animate-spin" />
                              Repair & Sync
                          </Button>
                      </div>
                 </div>
                  
                  <div class="pt-6 border-t">
                      <h3 class="text-lg font-medium text-red-600">Danger Zone</h3>
                      <p class="text-sm text-slate-500 mb-4">Once you delete a deck, there is no going back.</p>
                      <Button variant="destructive" @click="deleteDeck" :disabled="isDeleting">
                        <Loader2 v-if="isDeleting" class="w-4 h-4 mr-2 animate-spin" />
                        Delete Deck
                      </Button>
                 </div>
             </div>
        </TabsContent>
    </Tabs>
      <AlertDialog :open="isNoteDeleteOpen" @update:open="isNoteDeleteOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete this card?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently remove this note and its associated cards from the deck.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isNoteDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeDeleteNote" :disabled="isNoteDeleting" class="bg-red-600 hover:bg-red-700 focus:ring-red-600 text-white">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>
