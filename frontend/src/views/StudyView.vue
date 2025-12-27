<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import { toast } from 'vue-sonner'
import { Loader2, CheckCircle2, RotateCcw, AlertCircle } from 'lucide-vue-next'

import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const router = useRouter()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// State
const queue = ref([])
const currentIndex = ref(0)
const isRevealed = ref(false)
const isLoading = ref(true)
const isSubmitting = ref(false)
const extraCardsCount = ref(20)
const sessionStats = ref({
  total: 0,
  reviewed: 0,
  correct: 0
})

// Current Card Computed
const currentCard = computed(() => {
  if (!queue.value || queue.value.length === 0) return null
  return queue.value[currentIndex.value]
})

// Progress Computed
const progressPercentage = computed(() => {
  if (sessionStats.value.total === 0) return 0
  return (sessionStats.value.reviewed / sessionStats.value.total) * 100
})

// Fetch Due Cards
const fetchDueCards = async (force = false, limit = 50) => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  const query = new URLSearchParams({ limit: limit.toString() })
  if (force === true) query.append('ignore_limits', '1')
  
  try {
    const { data } = await useFetch(`${API_BASE_URL}/review/due?${query.toString()}`, {
      headers: { Authorization: `Bearer ${token}` }
    }).json()

    if (data.value) {
      // Handle potential difference between contract (card_id) and actual (id)
      // Assuming controller returns default model serialization, so 'id'.
      queue.value = data.value.cards || []
      sessionStats.value.total = queue.value.length
    }
  } catch (err) {
    toast.error('Failed to load cards', { description: 'Please try again later.' })
  } finally {
    isLoading.value = false
  }
}

// Reveal Answer
const revealAnswer = () => {
  isRevealed.value = true
}

// Submit Review (SM-2)
const submitReview = async (quality) => {
  if (!currentCard.value || isSubmitting.value) return

  isSubmitting.value = true
  const token = localStorage.getItem('auth_token')
  const cardId = currentCard.value.id || currentCard.value.card_id

  try {
    const { error } = await useFetch(`${API_BASE_URL}/review/${cardId}`, {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ quality })
    }).json()

    if (error.value) throw new Error('Review failed')

    // On Success
    sessionStats.value.reviewed++
    if (quality >= 3) sessionStats.value.correct++

    // Move to next card
    if (currentIndex.value < queue.value.length - 1) {
      currentIndex.value++
      isRevealed.value = false
    } else {
      // Session Complete
      queue.value = [] // clear queue to show summary
    }

  } catch (err) {
    toast.error('Failed to save review', { description: 'Your progress was not saved.' })
  } finally {
    isSubmitting.value = false
  }
}

// Keyboard Shortcuts
const handleKeydown = (e) => {
  if (isLoading.value || !currentCard.value) return
  
  if (e.code === 'Space') {
    e.preventDefault() // prevent scroll
    if (!isRevealed.value) {
      revealAnswer()
    }
  }

  if (isRevealed.value && !isSubmitting.value) {
    switch(e.key) {
      case '1': submitReview(1); break; // Again
      case '2': submitReview(2); break; // Hard
      case '3': submitReview(3); break; // Good
      case '4': submitReview(4); break; // Easy
    }
  }
}

onMounted(() => {
  fetchDueCards()
  window.addEventListener('keydown', handleKeydown)
})

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})

</script>

<template>
  <div class="min-h-full flex flex-col max-w-4xl mx-auto w-full p-4 lg:p-8">
    
    <!-- Header / Progress -->
    <div class="mb-8 space-y-2 shrink-0">
      <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
        <span class="font-medium">Daily Review</span>
        <span>{{ sessionStats.reviewed }} / {{ sessionStats.total }}</span>
      </div>
      <Progress :model-value="progressPercentage" class="h-2" />
    </div>

    <!-- Main Card Area -->
    <div class="flex-1 flex flex-col items-center w-full relative">
      
      <!-- Loading State -->
      <div v-if="isLoading" class="w-full max-w-2xl space-y-4 my-auto">
        <Skeleton class="h-[300px] w-full rounded-xl" />
        <div class="flex justify-center gap-4">
           <Skeleton class="h-12 w-24" />
           <Skeleton class="h-12 w-24" />
        </div>
      </div>

      <!-- Empty State / Session Complete -->
      <div v-else-if="!currentCard" class="text-center space-y-6 my-auto">
         <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mb-4">
            <CheckCircle2 class="w-10 h-10" />
         </div>
         <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Session Complete!</h2>
         <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto text-lg">
           You reviewed {{ sessionStats.reviewed }} cards today. Great job keeping up with your streak!
         </p>
         <div class="flex justify-center gap-4 pt-4">
             <Button @click="router.push('/dashboard')" size="lg" variant="outline">
                Back to Dashboard
             </Button>
             
             <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 p-1 rounded-lg border border-slate-200 dark:border-slate-800">
                <div class="relative w-20">
                    <Input 
                        type="number" 
                        min="1" 
                        max="100" 
                        v-model="extraCardsCount" 
                        class="h-10 border-0 bg-transparent text-center focus-visible:ring-0 focus-visible:ring-offset-0"
                    />
                </div>
                <Button @click="() => fetchDueCards(true, extraCardsCount)" size="lg">
                   <RotateCcw class="w-4 h-4 mr-2" /> Review More
                </Button>
             </div>
          </div>
      </div>

      <!-- Flashcard -->
      <Card v-else class="w-full max-w-2xl shadow-xl border-t-4 border-t-indigo-500 dark:bg-slate-900 my-auto mb-8">
        <CardHeader>
           <div class="flex justify-between items-center">
              <Badge variant="outline" class="uppercase tracking-wide text-[10px]">{{ currentCard.template?.name || 'Standard' }}</Badge>
              <span class="text-xs text-slate-400">Press Space to reveal</span>
           </div>
        </CardHeader>
        
        <CardContent class="py-6 px-6 min-h-[200px] flex flex-col gap-6 max-h-[60vh] overflow-y-auto w-full">
            <!-- Front -->
            <div class="prose dark:prose-invert max-w-none text-center w-full break-words">
               <div class="text-xl font-medium" v-html="currentCard.front_html"></div>
            </div>

            <!-- Answer Divider -->
            <div v-if="isRevealed" class="w-full flex items-center gap-4 animate-in fade-in duration-300">
               <Separator class="flex-1" />
               <span class="text-xs text-slate-400 uppercase">Answer</span>
               <Separator class="flex-1" />
            </div>

            <!-- Back -->
            <div v-if="isRevealed" class="prose dark:prose-invert max-w-none w-full break-words animate-in slide-in-from-bottom-2 duration-300 text-left">
                <div class="text-lg" v-html="currentCard.back_html"></div>
            </div>
        </CardContent>

        <CardFooter class="flex flex-col gap-4 border-t bg-slate-50/50 dark:bg-slate-950/30 p-6">
            <!-- Reveal Button -->
            <Button 
              v-if="!isRevealed" 
              @click="revealAnswer" 
              class="w-full h-12 text-lg font-semibold tracking-wide" 
              size="lg"
            >
              Show Answer
            </Button>

            <!-- Rating Buttons -->
            <div v-else class="grid grid-cols-4 gap-3 w-full animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div class="flex flex-col gap-1 group">
                    <Button 
                        @click="submitReview(1)" 
                        variant="outline" 
                        class="h-14 hover:bg-rose-100 hover:text-rose-700 hover:border-rose-200 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 border-b-4 active:border-b-0 active:translate-y-1"
                        :disabled="isSubmitting"
                    >
                        Again
                    </Button>
                    <span class="text-[10px] text-center text-slate-400 group-hover:text-slate-600 uppercase font-bold tracking-wider">&lt; 1m</span>
                </div>

                <div class="flex flex-col gap-1 group">
                    <Button 
                        @click="submitReview(2)" 
                        variant="outline" 
                        class="h-14 hover:bg-orange-100 hover:text-orange-700 hover:border-orange-200 dark:hover:bg-orange-900/30 dark:hover:text-orange-400 border-b-4 active:border-b-0 active:translate-y-1"
                         :disabled="isSubmitting"
                    >
                        Hard
                    </Button>
                    <span class="text-[10px] text-center text-slate-400 group-hover:text-slate-600 uppercase font-bold tracking-wider">2d</span>
                </div>

                <div class="flex flex-col gap-1 group">
                    <Button 
                        @click="submitReview(3)" 
                        variant="outline" 
                        class="h-14 hover:bg-blue-100 hover:text-blue-700 hover:border-blue-200 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 border-b-4 active:border-b-0 active:translate-y-1"
                         :disabled="isSubmitting"
                    >
                        Good
                    </Button>
                    <span class="text-[10px] text-center text-slate-400 group-hover:text-slate-600 uppercase font-bold tracking-wider">4d</span>
                </div>

                <div class="flex flex-col gap-1 group">
                    <Button 
                        @click="submitReview(4)" 
                        variant="outline" 
                        class="h-14 hover:bg-emerald-100 hover:text-emerald-700 hover:border-emerald-200 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400 border-b-4 active:border-b-0 active:translate-y-1"
                         :disabled="isSubmitting"
                    >
                        Easy
                    </Button>
                    <span class="text-[10px] text-center text-slate-400 group-hover:text-slate-600 uppercase font-bold tracking-wider">7d</span>
                </div>
            </div>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>

<style scoped>
/* Add any view-specific transitions here if needed */
</style>
