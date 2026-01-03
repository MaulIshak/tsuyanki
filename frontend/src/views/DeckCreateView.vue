<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { toast } from 'vue-sonner'

const router = useRouter()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

const form = ref({
  name: '',
  description: ''
})
const isSubmitting = ref(false)

const onSubmit = async () => {
    if (!form.value.name) {
        toast.error('Deck name is required')
        return
    }

    isSubmitting.value = true
    const token = localStorage.getItem('auth_token')

    try {
        const { data, error } = await useFetch(`${API_BASE_URL}/decks`, {
            method: 'POST',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: form.value.name,
                description: form.value.description
            })
        }).json()

        if (error.value) throw new Error(error.value)
        
        toast.success('Deck created successfully')
        router.push('/decks')
    } catch (e) {
        toast.error('Failed to create deck')
    } finally {
        isSubmitting.value = false
    }
}
</script>

<template>
  <div class="p-4 lg:p-6 max-w-2xl mx-auto space-y-4 sm:space-y-6">
    <Button variant="ghost" class="pl-0 gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100" @click="router.back()">
        <ArrowLeft class="w-4 h-4" /> Back
    </Button>

    <Card>
        <CardHeader>
            <CardTitle>Create New Deck</CardTitle>
            <CardDescription>Create a collection of cards to study a specific topic.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Name</label>
                <Input v-model="form.name" placeholder="e.g. Japanese Core 2000" />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Description (Optional)</label>
                <Textarea v-model="form.description" placeholder="What is this deck about?" class="resize-none h-32" />
            </div>
        </CardContent>
        <CardFooter class="flex justify-end gap-2">
            <Button variant="ghost" @click="router.back()">Cancel</Button>
            <Button @click="onSubmit" :disabled="isSubmitting">
                <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                <Save v-else class="w-4 h-4 mr-2" />
                Create Deck
            </Button>
        </CardFooter>
    </Card>
  </div>
</template>
