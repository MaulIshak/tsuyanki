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
const fields = ref({})
const tags = ref('')
const isLoading = ref(false)
const isSubmitting = ref(false)

// Computed
const activeFields = computed(() => {
    const type = noteTypes.value.find(t => t.id === selectedNoteTypeId.value)
    if (!type || !type.field_schema || !type.field_schema.fields) return []
    return type.field_schema.fields
})

// Fetch Note Types
const fetchNoteTypes = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        const { data } = await useFetch(`${API_BASE_URL}/note-types?per_page=100`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value && data.value.data) {
            noteTypes.value = data.value.data
            // Select default if available and not editing
            if (!noteId && noteTypes.value.length > 0) {
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
             if (note.tags && Array.isArray(note.tags)) {
                 // Format tags if they come as array of objects or strings. 
                 // Assuming simple array of strings or logic needed later.
             }
        }
    } catch (e) {
         toast.error('Failed to load note')
    } finally {
        isLoading.value = false
    }
}

const onSubmit = async () => {
    // Basic validation: ensure at least one field is filled or check required fields
    const filledCount = Object.values(fields.value).filter(v => v && v.trim()).length
    if (filledCount === 0) {
        toast.error('Please fill in at least one field')
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
            // Clear fields but keep type selection
            fields.value = {}
            tags.value = ''
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
  <div class="p-4 lg:p-6 max-w-3xl mx-auto space-y-6">
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

            <!-- Dynamic Fields -->
            <div class="space-y-4" v-if="activeFields.length > 0">
                 <div v-for="field in activeFields" :key="field.name" class="space-y-2">
                    <label class="text-sm font-medium">{{ field.name }}</label>
                    <Textarea 
                        v-model="fields[field.name]" 
                        :placeholder="`${field.name} content...`" 
                        class="resize-none font-medium"
                        :class="field.name.toLowerCase().includes('expression') || field.name.toLowerCase().includes('front') ? 'h-24 text-lg' : 'h-16'" 
                    />
                 </div>
            </div>
            <div v-else class="py-8 text-center text-slate-500">
                <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin mx-auto" />
                <span v-else>Select a note type to see fields.</span>
            </div>

             <!-- Tags (Optional) -->
             <div class="space-y-2">
                <label class="text-sm font-medium">Tags (comma separated)</label>
                <Input v-model="tags" placeholder="e.g. jlpt, grammar, verb" />
            </div>

        </CardContent>
        <CardFooter class="flex flex-col-reverse sm:flex-row justify-between sm:items-center gap-4">
             <span class="text-xs text-slate-400 italic text-center sm:text-left">
                * Cards are generated based on the selected Type.
             </span>
            <div class="flex gap-2 w-full sm:w-auto">
                 <Button variant="ghost" class="flex-1 sm:flex-none" @click="router.back()">Cancel</Button>
                 <Button class="flex-1 sm:flex-none" @click="onSubmit" :disabled="isSubmitting">
                    <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                    <Save v-else class="w-4 h-4 mr-2" />
                    {{ noteId ? 'Save Changes' : 'Add Note' }}
                </Button>
            </div>
        </CardFooter>
    </Card>
  </div>
</template>
