<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { ArrowLeft, Plus, MoreHorizontal, Settings, FileText, Trash2, Pencil, Loader2 } from 'lucide-vue-next'
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
import { toast } from 'vue-sonner'

const router = useRouter()
const route = useRoute()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
const deckId = route.params.id

// State
const deck = ref(null)
const notes = ref([])
const isLoading = ref(true)

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

// Fetch Notes in Deck
const fetchNotes = async () => {
    isLoading.value = true
    const token = localStorage.getItem('auth_token')
    try {
        // Assuming there is an endpoint to get notes filtered by deck or accessing via deck relationship
        // If not, we might need to rely on the deck response if it includes cards, 
        // BUT for a scalable management view, pagination is key.
        // Let's assumme GET /notes?deck_id=ID for now as per plan/contract implication
        // Use the nested route as defined in api.php
        const { data } = await useFetch(`${API_BASE_URL}/decks/${deckId}/notes?limit=100`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
             notes.value = data.value.data
        }
    } catch (e) {
        toast.error('Failed to load cards')
    } finally {
        isLoading.value = false
    }
}

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
        // Update local state without refetching
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
    await Promise.all([fetchDeck(), fetchNotes()])
})

watch(deck, (newDeck) => {
    if (newDeck) {
        editForm.value = { 
            title: newDeck.title, 
            description: newDeck.description 
        }
    }
}, { immediate: true })

</script>

<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6">
    
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
             <!-- Search/Filter Bar could go here -->
            
            <div class="rounded-md border bg-white dark:bg-slate-900">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[300px]">Front</TableHead>
                            <TableHead class="w-[300px]">Back</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="isLoading" v-for="i in 5" :key="i">
                             <TableCell><Skeleton class="h-4 w-20" /></TableCell>
                             <TableCell><Skeleton class="h-4 w-32" /></TableCell>
                             <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                             <TableCell><Skeleton class="h-8 w-8 ml-auto" /></TableCell>
                        </TableRow>
                        
                        <TableRow v-else-if="notes.length === 0">
                            <TableCell colspan="4" class="h-24 text-center">
                                No cards found. Start by adding one!
                            </TableCell>
                        </TableRow>

                        <TableRow v-else v-for="note in notes" :key="note.id">
                            <TableCell class="font-medium truncate max-w-[300px]" v-html="note.fields?.Front || note.fields?.front || note.front_html || 'N/A'"></TableCell>
                            <TableCell class="truncate max-w-[300px]" v-html="note.fields?.Back || note.fields?.back || note.back_html || 'N/A'"></TableCell>
                            <TableCell>
                                <Badge variant="secondary" class="text-xs">{{ note.type?.name || 'Basic' }}</Badge>
                            </TableCell>
                            <TableCell class="text-right">
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
                                        <DropdownMenuItem class="text-red-600 focus:text-red-600" @click="confirmDeleteNote(note)">
                                            <Trash2 class="mr-2 h-4 w-4" /> Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
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
