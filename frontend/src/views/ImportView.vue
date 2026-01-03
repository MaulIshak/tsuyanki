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
  <div class="p-4 sm:p-6 max-w-3xl mx-auto space-y-6 sm:space-y-10">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Import from Anki</h1>
                <Badge variant="secondary" class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800">Beta</Badge>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-lg">Migrate your decks and cards effortlessly.</p>
        </div>
        <!-- <Button variant="outline" @click="router.back()" class="hidden sm:flex">
            <ArrowLeft class="w-4 h-4 mr-2" /> Back
        </Button> -->
    </div>

    <!-- Stepper / Progress Indicator -->
    <div class="grid grid-cols-3 gap-4">
        <div v-for="step in steps" :key="step.id" class="flex flex-col gap-2 group">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-bold transition-all duration-300"
                    :class="currentStep >= step.id ? 'border-indigo-600 bg-indigo-600 text-white shadow-md' : 'border-slate-200 bg-white dark:bg-slate-900 text-slate-400 dark:border-slate-700'">
                    <CheckCircle2 v-if="currentStep > step.id" class="w-5 h-5" />
                    <span v-else>{{ step.id + 1 }}</span>
                </div>
                <div class="flex flex-col">
                     <span class="font-semibold text-sm transition-colors" :class="currentStep >= step.id ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-600'">{{ step.title }}</span>
                     <span class="text-xs text-slate-500 hidden sm:block">{{ step.description }}</span>
                </div>
            </div>
            <div class="h-1 w-full rounded-full bg-slate-100 dark:bg-slate-800 absolute bottom-0 left-0 hidden"></div>
        </div>
    </div>

    <!-- Interface Card -->
    <Card class="overflow-hidden shadow-xl border-slate-100 dark:border-slate-800 transition-all duration-300 bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm">
        
        <!-- Step 1: Upload -->
        <div v-if="currentStep === 0" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <CardHeader class="pb-2">
                <CardTitle>Select File</CardTitle>
                <CardDescription>Upload an .apkg file exported from Anki. This will import notes, cards, and media.</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                
                <Alert variant="default" class="bg-blue-50 border-blue-100 text-blue-800 dark:bg-blue-950/20 dark:border-blue-900/50 dark:text-blue-200">
                    <AlertCircle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <AlertTitle>Import Note</AlertTitle>
                    <AlertDescription class="text-blue-700/80 dark:text-blue-300/80">
                        Importing creates new decks. Existing decks with different IDs are safe. Large media collections might take a minute.
                    </AlertDescription>
                </Alert>

                <div 
                    class="relative border-4 border-dashed rounded-2xl p-6 sm:p-12 transition-all cursor-pointer group flex flex-col items-center justify-center min-h-[300px]"
                    :class="[
                        isDragging ? 'border-indigo-500 bg-indigo-50/50 scale-[1.01]' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900/50 dark:hover:border-slate-700',
                        file ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/10 dark:border-emerald-800' : ''
                    ]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    @click="$refs.fileInput.click()"
                >
                    <input ref="fileInput" type="file" accept=".apkg" class="hidden" @change="handleFileSelect" />
                    
                    <div class="flex flex-col items-center gap-6 text-center z-10 transition-transform duration-300 group-hover:-translate-y-1">
                        <div class="p-6 rounded-full transition-all duration-300 shadow-sm"
                             :class="file ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 group-hover:bg-white group-hover:shadow-md dark:group-hover:bg-slate-700'">
                             <FileType v-if="file" class="w-12 h-12" />
                             <UploadCloud v-else class="w-12 h-12" />
                        </div>
                        
                        <div v-if="file" class="space-y-1">
                             <h3 class="font-bold text-xl text-emerald-700 dark:text-emerald-400">{{ file.name }}</h3>
                             <p class="text-sm text-slate-500 font-medium">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
                             <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-400 hover:text-slate-600 transition-colors">Click to replace</div>
                        </div>
                        <div v-else class="space-y-2">
                            <h3 class="font-bold text-xl text-slate-900 dark:text-slate-100">Drag & Drop your .apkg file</h3>
                            <p class="text-slate-500 max-w-xs mx-auto">or click anywhere in this box to browse your files.</p>
                        </div>
                    </div>
                </div>

                <Alert v-if="error" variant="destructive" class="border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/50">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Import Error</AlertTitle>
                    <AlertDescription>{{ error }}</AlertDescription>
                </Alert>

            </CardContent>
            <CardFooter class="flex flex-col sm:flex-row gap-4 sm:gap-0 justify-between border-t bg-slate-50/50 dark:bg-slate-950/30 p-6">
                <!-- Helper text -->
                <p class="text-xs text-slate-400 w-full sm:max-w-[60%] text-center sm:text-left order-2 sm:order-1 leading-relaxed">
                    Need help exporting from Anki? Go to <span class="font-semibold text-slate-600 dark:text-slate-300">File > Export...</span> and select "Anki Deck Package (*.apkg)".
                </p>
                <Button @click="onUpload" :disabled="!file" size="lg" class="w-full sm:w-auto shadow-lg shadow-indigo-200 dark:shadow-none bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 order-1 sm:order-2 transition-all hover:scale-105">
                    Start Import <ArrowRight class="w-4 h-4 ml-2" />
                </Button>
            </CardFooter>
        </div>

        <!-- Step 2: Processing -->
        <div v-else-if="currentStep === 1" class="py-20 px-6 flex flex-col items-center justify-center text-center animate-in fade-in zoom-in-95 duration-500 min-h-[400px]">
             <div class="relative mb-10 scale-125">
                 <Loader2 class="w-20 h-20 text-indigo-200 dark:text-indigo-900 animate-spin" />
                 <div class="absolute inset-0 flex items-center justify-center font-bold text-sm text-indigo-600 dark:text-indigo-400">{{ progress }}%</div>
             </div>
             <h2 class="text-3xl font-bold mb-4 text-slate-900 dark:text-slate-100">Importing your data...</h2>
             <p class="text-slate-500 max-w-md mb-10 text-lg">Please wait while we unzip your package, parse the database, and generate your cards. This may take a few moments.</p>
             <div class="w-full max-w-md space-y-2">
                 <div class="flex justify-between text-xs text-slate-400 font-medium uppercase tracking-wider">
                     <span>Processing</span>
                     <span>{{ progress }}%</span>
                 </div>
                 <Progress :model-value="progress" class="h-3 bg-slate-100 dark:bg-slate-800" />
             </div>
        </div>

        <!-- Step 3: Result -->
        <div v-else-if="currentStep === 2" class="animate-in fade-in slide-in-from-right-4 duration-500">
            <CardHeader class="border-b bg-emerald-50/30 dark:bg-emerald-900/10 p-8">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                    <div class="p-4 bg-emerald-100 text-emerald-600 rounded-full dark:bg-emerald-900/30 ring-8 ring-emerald-50 dark:ring-emerald-950/50">
                        <CheckCircle2 class="w-10 h-10" />
                    </div>
                    <div class="space-y-1">
                        <CardTitle class="text-2xl text-emerald-800 dark:text-emerald-400">Import Complete!</CardTitle>
                        <CardDescription class="text-base">Your Anki collection has been successfully migrated.</CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <Table>
                    <TableHeader class="bg-slate-50 dark:bg-slate-900/50">
                        <TableRow class="hover:bg-transparent border-slate-100 dark:border-slate-800">
                            <TableHead class="pl-8 py-4 w-1/2">Resource</TableHead>
                            <TableHead class="text-right py-4">Count</TableHead>
                            <TableHead class="text-right pr-8 py-4">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow class="hover:bg-slate-50/50 border-slate-100 dark:border-slate-800">
                            <TableCell class="font-medium pl-8 py-4 text-slate-700 dark:text-slate-200">Decks</TableCell>
                            <TableCell class="text-right py-4 font-mono text-slate-600 dark:text-slate-400">{{ result.decks }}</TableCell>
                            <TableCell class="text-right pr-8 py-4"><Badge variant="outline" class="text-emerald-600 border-emerald-200 bg-emerald-50">Created</Badge></TableCell>
                        </TableRow>
                        <TableRow class="hover:bg-slate-50/50 border-slate-100 dark:border-slate-800">
                            <TableCell class="font-medium pl-8 py-4 text-slate-700 dark:text-slate-200">Notes</TableCell>
                            <TableCell class="text-right py-4 font-mono text-slate-600 dark:text-slate-400">{{ result.notes }}</TableCell>
                            <TableCell class="text-right pr-8 py-4"><Badge variant="outline" class="text-emerald-600 border-emerald-200 bg-emerald-50">Created</Badge></TableCell>
                        </TableRow>
                        <TableRow class="hover:bg-slate-50/50 border-slate-100 dark:border-slate-800">
                            <TableCell class="font-medium pl-8 py-4 text-slate-700 dark:text-slate-200">Cards</TableCell>
                            <TableCell class="text-right py-4 font-mono text-slate-600 dark:text-slate-400">{{ result.cards }}</TableCell>
                            <TableCell class="text-right pr-8 py-4"><Badge variant="outline" class="text-emerald-600 border-emerald-200 bg-emerald-50">Generated</Badge></TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
            <CardFooter class="flex flex-col-reverse sm:flex-row justify-between items-center p-6 bg-slate-50/30 dark:bg-slate-900/30 border-t gap-3 sm:gap-0">
                 <Button variant="outline" @click="reset" class="w-full sm:w-auto border-slate-200 text-slate-600 hover:bg-white hover:border-slate-300">Import Another</Button>
                 <Button @click="router.push('/decks')" class="w-full sm:w-auto shadow-md shadow-indigo-100 dark:shadow-none bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">View Decks <ArrowRight class="w-4 h-4 ml-2" /></Button>
            </CardFooter>
        </div>

    </Card>
  </div>
</template>
