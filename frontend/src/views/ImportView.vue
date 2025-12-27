<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { UploadCloud, FileUp, Loader2, CheckCircle2, AlertCircle, ArrowLeft, ArrowRight, FileType } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Progress } from '@/components/ui/progress'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { toast } from 'vue-sonner'

const router = useRouter()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// Steps: 0 = Upload, 1 = Processing, 2 = Result
const currentStep = ref(0)
const steps = [
    { id: 0, title: 'Upload', description: 'Select your Anki package' },
    { id: 1, title: 'Importing', description: 'Processing data' },
    { id: 2, title: 'Summary', description: 'Review results' }
]

const file = ref(null)
const isDragging = ref(false)
const isUploading = ref(false)
const progress = ref(0)
const result = ref(null)
const error = ref(null)

const handleFileSelect = (event) => {
    const selected = event.target.files[0]
    validateAndSetFile(selected)
}

const handleDrop = (event) => {
    isDragging.value = false
    const selected = event.dataTransfer.files[0]
    validateAndSetFile(selected)
}

const validateAndSetFile = (selected) => {
    if (selected && selected.name.endsWith('.apkg')) {
        file.value = selected
        error.value = null
    } else {
        toast.error('Invalid file type', { description: 'Please select a valid .apkg file' })
    }
}

const reset = () => {
    file.value = null
    result.value = null
    error.value = null
    currentStep.value = 0
    progress.value = 0
}

const onUpload = async () => {
    if (!file.value) return

    currentStep.value = 1
    isUploading.value = true
    error.value = null
    progress.value = 10 // Start progress

    // Simulate progress for UX 
    const interval = setInterval(() => {
        if (progress.value < 90) progress.value += 5
    }, 200)

    const formData = new FormData()
    formData.append('file', file.value)

    const token = localStorage.getItem('auth_token')

    try {
        const { data, response, error: fetchError } = await useFetch(`${API_BASE_URL}/import/anki`, {
            method: 'POST',
            body: formData,
            headers: { 'Authorization': `Bearer ${token}` }
        }).json()

        clearInterval(interval)
        progress.value = 100

        if (fetchError.value || !response.value.ok) {
            throw new Error(data.value?.message || fetchError.value || 'Upload failed')
        }

        // Wait a small moment for 100% bar to be seen
        setTimeout(() => {
            result.value = data.value
            currentStep.value = 2
            isUploading.value = false
            toast.success('Import successful!')
        }, 500)

    } catch (e) {
        clearInterval(interval)
        isUploading.value = false
        // Return to step 0 but keep error
        currentStep.value = 0
        error.value = e.message || 'An unexpected error occurred during import.'
        toast.error('Import failed', { description: error.value })
    }
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-3xl mx-auto space-y-6 sm:space-y-8">
    
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Import from Anki</h1>
            <p class="text-slate-500 dark:text-slate-400">Migrate your decks and cards effortlessly.</p>
        </div>
    </div>

    <!-- Stepper / Progress Indicator -->
    <div class="grid grid-cols-3 gap-4">
        <div v-for="step in steps" :key="step.id" class="flex flex-col gap-2">
            <div class="flex items-center gap-2" :class="currentStep >= step.id ? 'text-primary' : 'text-slate-400'">
                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors"
                    :class="currentStep >= step.id ? 'border-primary bg-primary text-primary-foreground' : 'border-slate-200 bg-transparent'">
                    <CheckCircle2 v-if="currentStep > step.id" class="w-4 h-4" />
                    <span v-else>{{ step.id + 1 }}</span>
                </div>
                <span class="font-medium text-sm hidden sm:block">{{ step.title }}</span>
            </div>
            <div class="h-1 w-full rounded-full bg-slate-100 dark:bg-slate-800 absolute bottom-0 left-0 hidden">
                 <!-- Line could go here if we wanted connected stepper -->
            </div>
        </div>
    </div>

    <!-- Interface Card -->
    <Card class="overflow-hidden shadow-lg border-slate-200 dark:border-slate-800 transition-all duration-300">
        
        <!-- Step 1: Upload -->
        <div v-if="currentStep === 0" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <CardHeader class="mb-2">
                <CardTitle>Select File</CardTitle>
                <CardDescription>Upload an .apkg file exported from Anki. This will import notes, cards, and media.</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                
                <Alert variant="default" class="bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-950/30 dark:border-blue-900 dark:text-blue-300">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Important</AlertTitle>
                    <AlertDescription>
                        Importing is a safe operation. It will create new decks and cards. Your existing Tsuyanki decks will not be overwritten unless they share exact IDs (which is rare).
                    </AlertDescription>
                </Alert>

                <div 
                    class="relative border-2 border-dashed rounded-xl p-6 sm:p-12 transition-all cursor-pointer group"
                    :class="[
                        isDragging ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-slate-200 hover:border-primary/50 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900',
                        file ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-900/10 dark:border-emerald-800' : ''
                    ]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    @click="$refs.fileInput.click()"
                >
                    <input ref="fileInput" type="file" accept=".apkg" class="hidden" @change="handleFileSelect" />
                    
                    <div class="flex flex-col items-center gap-4 text-center">
                        <div class="p-4 rounded-full transition-transform group-hover:scale-110 duration-300"
                             :class="file ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30' : 'bg-slate-100 text-slate-500 dark:bg-slate-800'">
                             <FileType v-if="file" class="w-8 h-8" />
                             <UploadCloud v-else class="w-8 h-8" />
                        </div>
                        
                        <div v-if="file">
                             <h3 class="font-semibold text-lg text-emerald-700 dark:text-emerald-400">{{ file.name }}</h3>
                             <p class="text-sm text-slate-500">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
                             <div class="mt-2 text-xs text-slate-400">Click to change file</div>
                        </div>
                        <div v-else>
                            <h3 class="font-semibold text-lg text-slate-900 dark:text-slate-100">Drag & Drop or Click to Upload</h3>
                            <p class="text-sm text-slate-500 mt-1">Accepts .apkg files only</p>
                        </div>
                    </div>
                </div>

                <Alert v-if="error" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>{{ error }}</AlertDescription>
                </Alert>

            </CardContent>
            <CardFooter class="flex flex-col sm:flex-row gap-4 sm:gap-0 justify-between border-t bg-slate-50/50 dark:bg-slate-900/50 p-4 sm:p-6">
                <!-- Helper text -->
                <p class="text-xs text-slate-400 w-full sm:max-w-[60%] text-center sm:text-left order-2 sm:order-1">
                    Need help exporting from Anki? Go to <span class="font-medium">File > Export...</span> and select "Anki Deck Package (*.apkg)".
                </p>
                <Button @click="onUpload" :disabled="!file" size="lg" class="w-full sm:w-auto shadow-sm order-1 sm:order-2">
                    Start Import <ArrowRight class="w-4 h-4 ml-2" />
                </Button>
            </CardFooter>
        </div>

        <!-- Step 2: Processing -->
        <div v-else-if="currentStep === 1" class="py-12 px-6 flex flex-col items-center justify-center text-center animate-in fade-in zoom-in-95 duration-500">
             <div class="relative mb-8">
                 <Loader2 class="w-16 h-16 text-primary animate-spin" />
                 <div class="absolute inset-0 flex items-center justify-center font-bold text-xs">{{ progress }}%</div>
             </div>
             <h2 class="text-2xl font-bold mb-2">Importing your data...</h2>
             <p class="text-slate-500 max-w-md mb-8">Please wait while we unzip your package, parse the database, and generate your cards. This may take a few moments for large decks.</p>
             <div class="w-full max-w-md space-y-2">
                 <div class="flex justify-between text-xs text-slate-400">
                     <span>Processing...</span>
                     <span>{{ progress }}%</span>
                 </div>
                 <Progress :model-value="progress" class="h-2" />
             </div>
        </div>

        <!-- Step 3: Result -->
        <div v-else-if="currentStep === 2" class="animate-in fade-in slide-in-from-right-4 duration-500">
            <CardHeader class="border-b bg-emerald-50/30 dark:bg-emerald-900/10">
                <div class="flex items-center gap-4">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-full dark:bg-emerald-900/30">
                        <CheckCircle2 class="w-6 h-6" />
                    </div>
                    <div>
                        <CardTitle class="text-emerald-700 dark:text-emerald-400">Import Complete!</CardTitle>
                        <CardDescription>Your Anki collection has been successfully migrated.</CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="pl-6">Resource</TableHead>
                            <TableHead class="text-right">Count</TableHead>
                            <TableHead class="text-right pr-6">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow>
                            <TableCell class="font-medium pl-6">Decks</TableCell>
                            <TableCell class="text-right">{{ result.decks }}</TableCell>
                            <TableCell class="text-right pr-6"><Badge variant="outline" class="text-emerald-600 border-emerald-200 bg-emerald-50">Created</Badge></TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="font-medium pl-6">Notes</TableCell>
                            <TableCell class="text-right">{{ result.notes }}</TableCell>
                            <TableCell class="text-right pr-6"><Badge variant="outline" class="text-emerald-600 border-emerald-200 bg-emerald-50">Created</Badge></TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell class="font-medium pl-6">Cards</TableCell>
                            <TableCell class="text-right">{{ result.cards }}</TableCell>
                            <TableCell class="text-right pr-6"><Badge variant="outline" class="text-emerald-600 border-emerald-200 bg-emerald-50">Generated</Badge></TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
            <CardFooter class="flex flex-col-reverse sm:flex-row justify-between items-center p-4 sm:p-6 bg-slate-50/50 dark:bg-slate-900/50 border-t gap-3 sm:gap-0">
                 <Button variant="outline" @click="reset" class="w-full sm:w-auto">Import Another</Button>
                 <Button @click="router.push('/decks')" class="w-full sm:w-auto">View Decks <ArrowRight class="w-4 h-4 ml-2" /></Button>
            </CardFooter>
        </div>

    </Card>
  </div>
</template>
