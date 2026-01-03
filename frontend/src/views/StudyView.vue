<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import { toast } from 'vue-sonner'
import { Loader2, CheckCircle2, RotateCcw, AlertCircle, ArrowDown } from 'lucide-vue-next'

import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const router = useRouter()
const route = useRoute()
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

const userTypedAnswer = ref(null)
const correctAnswer = ref(null)

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
const fetchDueCards = async (force = false, limit = null) => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  
  // Use provided limit, or query param, or default 20
  const effectiveLimit = limit || route.query.limit || 20
  const query = new URLSearchParams({ limit: effectiveLimit.toString() })
  
  // If forcing (Review More) OR studying a specific deck (Custom Session), ignore global daily limits
  if (force === true || route.query.deck_id) {
      query.append('ignore_limits', '1')
  }

  if (route.query.deck_id) query.append('deck_id', route.query.deck_id)
  
  try {
    const { data } = await useFetch(`${API_BASE_URL}/review/due?${query.toString()}`, {
      headers: { Authorization: `Bearer ${token}` }
    }).json()

    if (data.value) {
      // Handle potential difference between contract (card_id) and actual (id)
      const newCards = data.value.cards || []
      
      queue.value = newCards
      
      if (force) {
          // If adding to existing session (Review More)
          sessionStats.value.total += newCards.length
      } else {
          // Initial load
          sessionStats.value.total = newCards.length
          sessionStats.value.reviewed = 0
          sessionStats.value.correct = 0
      }
      
      // Reset index for the new queue chunk
      currentIndex.value = 0
      isRevealed.value = false
      userTypedAnswer.value = null
      correctAnswer.value = null
    }
  } catch (err) {
    toast.error('Failed to load cards', { description: 'Please try again later.' })
  } finally {
    isLoading.value = false
  }
}

// Reveal Answer
import { nextTick } from 'vue'

const revealAnswer = () => {
  // 1. Try to grab input from Front card
  const inputEl = document.querySelector('input[placeholder="Type answer..."]')
  if (inputEl) {
      userTypedAnswer.value = inputEl.value
  } else {
      userTypedAnswer.value = null
  }

  isRevealed.value = true
  
  // 2. Try to grab hidden correct answer from Back card (after render)
  nextTick(() => {
      const hiddenSpan = document.getElementById('typeans-correct')
      if (hiddenSpan) {
          correctAnswer.value = hiddenSpan.innerText
      } else {
          correctAnswer.value = null
      }
  })
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
      userTypedAnswer.value = null
      correctAnswer.value = null
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
  <div class="absolute inset-0 z-0 bg-slate-100/50 dark:bg-black/20 pointer-events-none backdrop-blur-[2px]"></div>

  <div class="relative z-10 min-h-full flex flex-col max-w-4xl mx-auto w-full p-4 lg:p-8">
    
    <!-- Header / Progress -->
    <div class="mb-4 lg:mb-8 space-y-2 shrink-0 opacity-70 hover:opacity-100 transition-opacity duration-300">
      <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-300 font-medium">
        <span class="tracking-wide uppercase text-xs text-slate-400">Daily Goal</span>
        <span>{{ sessionStats.reviewed }} / {{ sessionStats.total }}</span>
      </div>
      <Progress :model-value="progressPercentage" class="h-2 bg-slate-200 dark:bg-slate-800 [&>div]:bg-indigo-500" />
    </div>

    <!-- Main Card Area -->
    <div class="flex-1 flex flex-col items-center w-full relative justify-center pb-12">
      
      <!-- Loading State -->
      <div v-if="isLoading" class="w-full max-w-2xl space-y-4">
        <Skeleton class="h-[400px] w-full rounded-2xl shadow-lg" />
        <div class="flex justify-center gap-4 mt-8">
           <Skeleton class="h-14 w-32 rounded-full" />
        </div>
      </div>

      <!-- Empty State / Session Complete -->
      <div v-else-if="!currentCard" class="text-center space-y-6 animate-in zoom-in-95 duration-500">
         <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-900/40 dark:to-emerald-800/20 text-emerald-600 dark:text-emerald-400 mb-4 shadow-inner ring-4 ring-emerald-50 dark:ring-emerald-950">
            <CheckCircle2 class="w-12 h-12" />
         </div>
         <h2 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">Session Complete!</h2>
         <p class="text-slate-600 dark:text-slate-300 max-w-md mx-auto text-lg">
           You reviewed {{ sessionStats.reviewed }} cards today. Great job keeping up with your streak!
         </p>
         <div class="flex flex-col sm:flex-row justify-center gap-4 pt-8 px-4 sm:px-0 w-full">
             <Button @click="router.push('/dashboard')" size="lg" variant="outline" class="w-full sm:w-auto order-2 sm:order-1 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">
                Back to Dashboard
             </Button>
             
             <div class="flex items-center gap-2 bg-white dark:bg-slate-900 p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 w-full sm:w-auto order-1 sm:order-2 shadow-sm">
                <div class="relative w-20 shrink-0">
                    <Input 
                        type="number" 
                        min="1" 
                        max="100" 
                        v-model="extraCardsCount" 
                        class="h-11 border-0 bg-transparent text-center focus-visible:ring-0 focus-visible:ring-offset-0 text-lg font-semibold"
                    />
                </div>
                <Button @click="() => fetchDueCards(true, extraCardsCount)" size="lg" class="flex-1 sm:flex-initial shadow-md bg-indigo-600 hover:bg-indigo-700 text-white border-0">
                   <RotateCcw class="w-4 h-4 mr-2" /> Review More
                </Button>
             </div>
          </div>
      </div>

      <!-- Flashcard -->
      <Card v-else class="w-full max-w-2xl shadow-2xl border-0 ring-1 ring-slate-900/5 dark:ring-white/10 dark:bg-slate-900/90 backdrop-blur-md overflow-hidden flex flex-col min-h-[400px] transition-all duration-300 hover:shadow-indigo-500/10">
        <CardHeader class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 py-3 px-6">
           <div class="flex justify-between items-center">
              <Badge variant="outline" class="uppercase tracking-wider text-[10px] font-semibold text-slate-500 border-slate-300 dark:border-slate-700">{{ currentCard.template?.name || 'Standard' }}</Badge>
              <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                  <span class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">Space</span>
                  to reveal
              </div>
           </div>
        </CardHeader>
        
        <CardContent class="py-8 px-8 flex-1 flex flex-col gap-8 w-full">
            <!-- Front -->
            <div class="prose dark:prose-invert max-w-none text-center w-full break-words">
               <div class="text-2xl md:text-3xl font-medium text-slate-800 dark:text-slate-100 leading-snug" v-html="currentCard.front_html"></div>
            </div>

            <!-- Answer Divider -->
            <div v-if="isRevealed" class="w-full flex items-center gap-4 animate-in fade-in zoom-in-95 duration-300">
               <Separator class="flex-1 bg-indigo-100 dark:bg-indigo-900/50" />
               <span class="text-[10px] text-indigo-300 uppercase tracking-[0.2em] font-bold">Answer</span>
               <Separator class="flex-1 bg-indigo-100 dark:bg-indigo-900/50" />
            </div>

            <!-- Type Answer Comparison -->
            <div v-if="isRevealed && userTypedAnswer !== null" class="w-full animate-in slide-in-from-bottom-2 duration-300">
                <div v-if="userTypedAnswer.trim().toLowerCase() === correctAnswer?.trim().toLowerCase()" class="bg-emerald-50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-300 p-4 rounded-xl border border-emerald-100 dark:border-emerald-900 text-center font-medium shadow-sm">
                    <CheckCircle2 class="w-5 h-5 inline-block mr-2 align-text-bottom" />
                    Correct! ({{ userTypedAnswer }})
                </div>
                <div v-else class="bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-300 p-4 rounded-xl border border-red-100 dark:border-red-900 text-center flex flex-col gap-2 shadow-sm">
                     <div class="line-through opacity-75 decoration-2 decoration-red-400">{{ userTypedAnswer || '(Empty)' }}</div>
                     <div class="font-bold flex items-center justify-center gap-2 text-lg">
                        <ArrowDown class="w-5 h-5 text-red-500" />
                        {{ correctAnswer }}
                     </div>
                </div>
            </div>

            <!-- Back -->
            <div v-if="isRevealed" class="prose dark:prose-invert max-w-none w-full break-words animate-in slide-in-from-bottom-4 duration-500 text-left bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-800">
                <div class="text-lg leading-relaxed text-slate-700 dark:text-slate-200" v-html="currentCard.back_html"></div>
            </div>
        </CardContent>

        <CardFooter class="flex flex-col gap-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 pt-6">
            <!-- Reveal Button -->
            <Button 
              v-if="!isRevealed" 
              @click="revealAnswer" 
              class="w-full h-14 text-lg font-bold tracking-wide shadow-lg shadow-indigo-200 dark:shadow-indigo-900/20 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all hover:scale-[1.01]" 
              size="lg"
            >
              Show Answer
            </Button>

            <!-- Rating Buttons -->
            <div v-else class="grid grid-cols-4 gap-3 md:gap-4 w-full animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="flex flex-col gap-1.5 group">
                    <Button 
                        @click="submitReview(1)" 
                        variant="outline" 
                        class="h-16 flex flex-col items-center justify-center gap-0.5 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 dark:hover:bg-rose-950/30 dark:hover:text-rose-400 border-b-4 active:border-b-0 active:translate-y-1 transition-all rounded-xl"
                        :disabled="isSubmitting"
                    >
                        <span class="font-bold text-base">Again</span>
                    </Button>
                    <span class="text-[10px] text-center text-slate-300 group-hover:text-rose-500 font-bold uppercase tracking-wider transition-colors">&lt; 1m</span>
                </div>

                <div class="flex flex-col gap-1.5 group">
                    <Button 
                        @click="submitReview(2)" 
                        variant="outline" 
                        class="h-16 flex flex-col items-center justify-center gap-0.5 hover:bg-orange-50 hover:text-orange-700 hover:border-orange-200 dark:hover:bg-orange-950/30 dark:hover:text-orange-400 border-b-4 active:border-b-0 active:translate-y-1 transition-all rounded-xl"
                         :disabled="isSubmitting"
                    >
                        <span class="font-bold text-base">Hard</span>
                    </Button>
                    <span class="text-[10px] text-center text-slate-300 group-hover:text-orange-500 font-bold uppercase tracking-wider transition-colors">2d</span>
                </div>

                <div class="flex flex-col gap-1.5 group">
                    <Button 
                        @click="submitReview(3)" 
                        variant="outline" 
                        class="h-16 flex flex-col items-center justify-center gap-0.5 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 dark:hover:bg-blue-950/30 dark:hover:text-blue-400 border-b-4 active:border-b-0 active:translate-y-1 transition-all rounded-xl"
                         :disabled="isSubmitting"
                    >
                        <span class="font-bold text-base">Good</span>
                    </Button>
                    <span class="text-[10px] text-center text-slate-300 group-hover:text-blue-500 font-bold uppercase tracking-wider transition-colors">4d</span>
                </div>

                <div class="flex flex-col gap-1.5 group">
                    <Button 
                        @click="submitReview(4)" 
                        variant="outline" 
                        class="h-16 flex flex-col items-center justify-center gap-0.5 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-400 border-b-4 active:border-b-0 active:translate-y-1 transition-all rounded-xl"
                         :disabled="isSubmitting"
                    >
                        <span class="font-bold text-base">Easy</span>
                    </Button>
                    <span class="text-[10px] text-center text-slate-300 group-hover:text-emerald-500 font-bold uppercase tracking-wider transition-colors">7d</span>
                </div>
            </div>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>

<style scoped>
/* Smooth typography */
.prose {
    font-feature-settings: "rlig" 1, "calt" 1;
}
</style>
