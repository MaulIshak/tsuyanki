<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { ArrowLeft, Save, Loader2, Plus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { toast } from 'vue-sonner'

const router = useRouter()
const route = useRoute()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
const deckId = route.params.deckId // For creating new note
const noteId = route.params.noteId // For editing existing note

// State
const noteTypes = ref([])
const selectedNoteTypeId = ref(null)
const fields = ref({
    front: '',
    back: ''
})
const tags = ref('')
const isLoading = ref(false)
const isSubmitting = ref(false)

// Fetch Note Types
const fetchNoteTypes = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        const { data } = await useFetch(`${API_BASE_URL}/note-types`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
            noteTypes.value = data.value.data
            // Select default if available (e.g., Basic)
            if (noteTypes.value.length > 0) {
                 // Try to find 'Basic' or default to first
                 const basic = noteTypes.value.find(t => t.name.toLowerCase().includes('basic'))
                 selectedNoteTypeId.value = basic ? basic.id : noteTypes.value[0].id
            }
        }
    } catch (e) {
        toast.error('Failed to load note types')
    }
}

// Fetch Note (if editing)
const fetchNote = async () => {
    if (!noteId) return
    isLoading.value = true
    const token = localStorage.getItem('auth_token')
    
    try {
         const { data } = await useFetch(`${API_BASE_URL}/notes/${noteId}`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
             const note = data.value
             selectedNoteTypeId.value = note.note_type_id
             fields.value = { ...note.fields } // Clone fields
             // Tags handling if needed
        }
    } catch (e) {
         toast.error('Failed to load note')
    } finally {
        isLoading.value = false
    }
}

const onSubmit = async () => {
    if (!fields.value.front || !fields.value.back) {
        toast.error('Front and Back fields are required')
        return
    }

    isSubmitting.value = true
    const token = localStorage.getItem('auth_token')

    try {
        let url, method
        
        if (noteId) {
             url = `${API_BASE_URL}/notes/${noteId}`
             method = 'PUT'
        } else {
             url = `${API_BASE_URL}/decks/${deckId}/notes`
             method = 'POST'
        }

        const payload = {
             note_type_id: selectedNoteTypeId.value,
             fields: fields.value,
             tags: tags.value ? tags.value.split(',').map(t => t.trim()).filter(Boolean) : []
        }

        const { data, error } = await useFetch(url, {
            method,
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }).json()

        if (error.value) throw new Error(error.value)
        
        toast.success(noteId ? 'Note updated' : 'Note created')
        
        if (!noteId) {
            // If creating, maybe user wants to add another?
            // For now, redirect back to deck
            // Confirm/Ask pattern: Clear form?
            fields.value.front = ''
            fields.value.back = ''
            // Optional: stay on page to add more
        } else {
            router.back()
        }

    } catch (e) {
        toast.error('Failed to save note')
        console.error(e)
    } finally {
        isSubmitting.value = false
    }
}

onMounted(() => {
    fetchNoteTypes()
    if (noteId) fetchNote()
})
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto space-y-6">
    <Button variant="ghost" class="pl-0 gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100" @click="router.back()">
        <ArrowLeft class="w-4 h-4" /> Back
    </Button>

    <Card>
        <CardHeader>
            <CardTitle>{{ noteId ? 'Edit Note' : 'Add New Note' }}</CardTitle>
            <CardDescription>
                {{ noteId ? 'Update the content of this note.' : 'Create a new note. Cards will be generated automatically.' }}
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            
            <!-- Note Type Selector -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Type</label>
                <Select v-model="selectedNoteTypeId" :disabled="!!noteId">
                    <SelectTrigger>
                        <SelectValue placeholder="Select a note type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="type in noteTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Fields -->
            <div class="space-y-4">
                 <div class="space-y-2">
                    <label class="text-sm font-medium">Front</label>
                    <Textarea v-model="fields.front" placeholder="Front content..." class="resize-none h-32 font-medium text-lg" />
                 </div>
                 
                 <div class="space-y-2">
                    <label class="text-sm font-medium">Back</label>
                    <Textarea v-model="fields.back" placeholder="Back content..." class="resize-none h-32" />
                 </div>
            </div>

             <!-- Tags (Optional) -->
             <div class="space-y-2">
                <label class="text-sm font-medium">Tags (comma separated)</label>
                <Input v-model="tags" placeholder="e.g. jlpt, grammar, verb" />
            </div>

        </CardContent>
        <CardFooter class="flex justify-between">
             <span class="text-xs text-slate-400 italic">
                * Cards are generated based on the selected Type.
             </span>
            <div class="flex gap-2">
                 <Button variant="ghost" @click="router.back()">Cancel</Button>
                 <Button @click="onSubmit" :disabled="isSubmitting">
                    <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                    <Save v-else class="w-4 h-4 mr-2" />
                    {{ noteId ? 'Save Changes' : 'Add Note' }}
                </Button>
            </div>
        </CardFooter>
    </Card>
  </div>
</template>
