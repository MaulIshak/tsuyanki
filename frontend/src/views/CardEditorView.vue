<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { ArrowLeft, Save, Loader2, Plus, X, Tag as TagIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import RichTextEditor from '@/components/RichTextEditor.vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { parseMedia, resolveMediaUrl, deserializeContent, serializeContent } from '@/lib/mediaUtils'

const router = useRouter()
const route = useRoute()

// Debug route injection
console.log('CardEditorView mounted. Route:', route)

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
const deckId = route?.params?.deckId
const noteId = route?.params?.noteId

// State
const noteTypes = ref([])
const selectedNoteTypeId = ref(null)
const fields = ref({})
// const tags = ref('') // Removed old tags string
const selectedTags = ref([]) // New tags array
const tagInput = ref('')
const scopedTags = ref([]) // Available tags in deck
const showSuggestions = ref(false)
const activeSuggestionIndex = ref(-1)

const isLoading = ref(false)
const isSubmitting = ref(false)
const fieldEditors = ref({}) // Refs to editors
const isUploading = ref(false)
const fileInput = ref(null)
const activeUploadField = ref(null)

// Computed
const activeFields = computed(() => {
    const type = noteTypes.value.find(t => t.id === selectedNoteTypeId.value)
    if (!type || !type.field_schema || !type.field_schema.fields) return []
    return type.field_schema.fields
})

const filteredSuggestions = computed(() => {
    if (!tagInput.value) return scopedTags.value
    return scopedTags.value.filter(tag => 
        tag.name.toLowerCase().includes(tagInput.value.toLowerCase()) && 
        !selectedTags.value.includes(tag.name)
    )
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
                 // Try to pick basic or first
                 const basic = noteTypes.value.find(t => t.name.toLowerCase().includes('basic'))
                 selectedNoteTypeId.value = basic ? basic.id : noteTypes.value[0].id
            }
        }
    } catch (e) {
        toast.error('Failed to load note types')
    }
}

// Fetch Scoped Tags
const fetchScopedTags = async (targetDeckId) => {
    if (!targetDeckId) return
    const token = localStorage.getItem('auth_token')
    try {
         console.log('Fetching scoped tags for deck:', targetDeckId)
         const { data } = await useFetch(`${API_BASE_URL}/tags?deck_id=${targetDeckId}&limit=100`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
            console.log('Scoped tags fetched:', data.value.data)
            scopedTags.value = data.value.data
        }
    } catch (e) {
        console.error('Failed to load tags', e)
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
             // Deserialize fields
             const rawFields = { ...note.fields }
             for (const key in rawFields) {
                 fields.value[key] = deserializeContent(rawFields[key])
             }
             
             // Set tags
             if (note.tags) {
                 selectedTags.value = note.tags.map(t => t.name)
             }

             // Start fetching scoped tags for THIS deck (note.deck_id)
             fetchScopedTags(note.deck_id)
             
             // Ensure note type exists
             if (note.note_type) {
                const typeExists = noteTypes.value.some(t => t.id === note.note_type.id)
                if (!typeExists) {
                    noteTypes.value.push(note.note_type)
                }
             }
        }
    } catch (e) {
         toast.error('Failed to load note')
    } finally {
        isLoading.value = false
    }
}

// Tag Logic
const addTag = () => {
    const val = tagInput.value.trim()
    if (!val) return
    
    // Check if exists in suggestions (case insensitive match for cleanup)
    const existing = scopedTags.value.find(t => t.name.toLowerCase() === val.toLowerCase())
    const nameToAdd = existing ? existing.name : val
    
    if (!selectedTags.value.includes(nameToAdd)) {
        selectedTags.value.push(nameToAdd)
    }
    tagInput.value = ''
    activeSuggestionIndex.value = -1
}

const selectTag = (tagName) => {
    if (!selectedTags.value.includes(tagName)) {
        selectedTags.value.push(tagName)
    }
    tagInput.value = ''
    showSuggestions.value = false
}

const removeTag = (tagName) => {
    selectedTags.value = selectedTags.value.filter(t => t !== tagName)
}

const navigateSuggestions = (step) => {
    if (!showSuggestions.value) return
    const len = filteredSuggestions.value.length
    if (len === 0) return
    activeSuggestionIndex.value = (activeSuggestionIndex.value + step + len) % len
}

const handleInputBlur = () => {
    // Delay hiding suggestions to allow click event on suggestion item
    setTimeout(() => {
        showSuggestions.value = false
    }, 200)
}

// Watch ENTER on suggestion
watch(activeSuggestionIndex, (idx) => {
     // Optional: Scroll to active item?
})

const onSubmit = async () => {
    // Basic validation
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

        // Serialize fields
        const serializedFields = {}
        for (const key in fields.value) {
            serializedFields[key] = serializeContent(fields.value[key])
        }

        const payload = {
             note_type_id: selectedNoteTypeId.value,
             fields: serializedFields,
             tags: selectedTags.value // Send array of strings
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
        
        toast.success(noteId ? 'Card updated' : 'Card created')
        
        if (!noteId) {
            // Clear fields but keep type selection
            fields.value = {}
            selectedTags.value = []
            // Refresh tags so the new one is available
            await fetchScopedTags(deckId)
            // tags.value = ''
        } else {
            router.back()
        }

    } catch (e) {
        toast.error('Failed to save card')
        console.error(e)
    } finally {
        isSubmitting.value = false
    }
}

const handleEditorUploadRequest = (fieldName) => {
    // Current approach: trigger hidden input, but using Editor's insert command after upload
    activeUploadField.value = fieldName
    fileInput.value.click()
}

const onFileSelected = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    isUploading.value = true
    const formData = new FormData()
    formData.append('file', file)

    try {
        const token = localStorage.getItem('auth_token')
        const { data, error } = await useFetch(`${API_BASE_URL}/media/upload`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token}` },
            body: formData
        }).json()

        if (error.value) throw new Error(error.value)

        const media = data.value
        const fullUrl = resolveMediaUrl(media.storage_key)
        
        const editorInstance = fieldEditors.value[activeUploadField.value]
        
        if (editorInstance) {
             let type = 'image'
             if (file.type.startsWith('audio/')) type = 'audio'
             else if (file.type.startsWith('video/')) type = 'video'
             
             editorInstance.insertMedia(type, fullUrl)
             toast.success('Media uploaded')
        }
        
    } catch (e) {
        console.error(e)
        // toast.error('Failed to upload media')
    } finally {
        isUploading.value = false
        if (fileInput.value) fileInput.value.value = '' 
    }
}

const getFieldMedia = (content) => {
    return parseMedia(content)
}

onMounted(() => {
    fetchNoteTypes()
    if (noteId) {
        fetchNote()
    } else if (deckId) {
        fetchScopedTags(deckId)
    }
})
</script>

<template>
  <div class="p-3 lg:p-6 max-w-3xl mx-auto space-y-4 sm:space-y-6">
    <Button variant="ghost" class="pl-0 gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100" @click="router.back()">
        <ArrowLeft class="w-4 h-4" /> Back
    </Button>

    <Card>
        <CardHeader class="pb-4">
            <CardTitle class="text-xl sm:text-2xl">{{ noteId ? 'Edit Card' : 'Add New Card' }}</CardTitle>
            <CardDescription class="text-xs sm:text-sm">
                {{ noteId ? 'Update the content of this card.' : 'Create a new card.' }}
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            
            <!-- Note Type Selector -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Type</label>
                <Select v-model="selectedNoteTypeId" :disabled="!!noteId">
                    <SelectTrigger>
                        <SelectValue placeholder="Select a card type" />
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
                 <div v-for="(field, idx) in activeFields" :key="field.name" class="space-y-1.5">
                    <label class="text-xs sm:text-sm font-medium">{{ field.name }}</label>
                    <RichTextEditor
                        :ref="(el) => { if (el) fieldEditors[field.name] = el }"
                        v-model="fields[field.name]"
                        :placeholder="`${field.name} content...`"
                        @on-media-upload="handleEditorUploadRequest(field.name)"
                    />
                 </div>
            </div>
            <div v-else class="py-8 text-center text-slate-500">
                <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin mx-auto" />
                <span v-else>Select a card type to see fields.</span>
            </div>

             <!-- Tags (Scoped to Deck) -->
             <div class="space-y-2">
                <label class="text-sm font-medium">Tags</label>
                <div class="flex flex-wrap gap-2 mb-2" v-if="selectedTags.length > 0">
                    <Badge v-for="tag in selectedTags" :key="tag" variant="secondary" class="gap-1 pr-1">
                        {{ tag }}
                        <div class="cursor-pointer rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 p-0.5" @click="removeTag(tag)">
                            <X class="w-3 h-3" />
                        </div>
                    </Badge>
                </div>
                <div class="relative w-full">
                    <div class="relative">
                        <TagIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
                        <Input 
                            v-model="tagInput" 
                            placeholder="Add a tag..." 
                            class="pl-9" 
                            @keydown.enter.prevent="addTag"
                            @keydown.down.prevent="navigateSuggestions(1)"
                            @keydown.up.prevent="navigateSuggestions(-1)"
                            @focus="showSuggestions = true"
                            @blur="handleInputBlur"
                        />
                    </div>
                    <!-- Suggestions Dropdown -->
                    <div v-if="showSuggestions && filteredSuggestions.length > 0" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-900 border rounded-md shadow-lg max-h-60 overflow-auto">
                        <div 
                            v-for="(tag, index) in filteredSuggestions" 
                            :key="tag.id"
                            class="px-4 py-2 text-sm cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800"
                            :class="{ 'bg-slate-100 dark:bg-slate-800': activeSuggestionIndex === index }"
                            @click="selectTag(tag.name)"
                        >
                            {{ tag.name }}
                        </div>
                    </div>
                     <div v-if="showSuggestions && tagInput && !filteredSuggestions.some(t => t.name.toLowerCase() === tagInput.toLowerCase())" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-900 border rounded-md shadow-lg p-2">
                        <div class="text-xs text-slate-500 px-2 py-1">Type Enter to create new tag "{{ tagInput }}"</div>
                    </div>
                </div>
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
                    {{ noteId ? 'Save Changes' : 'Add Card' }}
                </Button>
            </div>
        </CardFooter>
    </Card>
    <!-- Hidden File Input -->
     <input
        type="file"
        ref="fileInput"
        class="hidden"
        accept="image/*,audio/*,video/*"
        @change="onFileSelected"
    />
  </div>
</template>
