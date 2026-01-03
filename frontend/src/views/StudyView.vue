<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFetch } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { toast } from 'vue-sonner'
import { 
  Loader2, 
  CheckCircle2, 
  RotateCcw, 
  AlertCircle, 
  ArrowDown, 
  Calendar, 
  Layers, 
  Play,
  ArrowLeft 
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// --- State ---
const viewMode = ref('menu') // 'menu' | 'session'
const decks = ref([])
const isFetchingDecks = ref(false)

// Menu Selection State
const selectedMode = ref('daily') // 'daily' | 'deck'
const selectedDeckId = ref('')
const customLimit = ref(20)

// Session State
const queue = ref([])
const currentIndex = ref(0)
const isRevealed = ref(false)
const isLoading = ref(false)
const isSubmitting = ref(false)
const extraCardsCount = ref(20)
const sessionStats = ref({
  total: 0,
  reviewed: 0,
  correct: 0
})

const userTypedAnswer = ref(null)
const correctAnswer = ref(null)

// --- Computeds ---
const currentCard = computed(() => {
  if (!queue.value || queue.value.length === 0) return null
  return queue.value[currentIndex.value]
})

const progressPercentage = computed(() => {
  if (sessionStats.value.total === 0) return 0
  return (sessionStats.value.reviewed / sessionStats.value.total) * 100
})

// --- Data Fetching ---

const fetchDecks = async () => {
    isFetchingDecks.value = true
    const token = localStorage.getItem('auth_token')
    try {
        const url = new URL(`${API_BASE_URL}/decks`)
        url.searchParams.append('limit', '100') // Fetch more decks for the dropdown
        
        const { data } = await useFetch(url.toString(), {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
            // Handle paginated response (Laravel resource) or direct array
            decks.value = Array.isArray(data.value) ? data.value : (data.value.data || [])
        }
    } catch (e) {
        toast.error('Failed to load decks')
    } finally {
        isFetchingDecks.value = false
    }
}

// Fetch Due Cards (Refactored)
const fetchDueCards = async (force = false, limit = null, deckId = null) => {
  isLoading.value = true
  const token = localStorage.getItem('auth_token')
  
  const effectiveLimit = limit || 20
  const query = new URLSearchParams({ limit: effectiveLimit.toString() })
  
  // If forcing (Review More / Custom Study)
  if (force === true) {
      query.append('ignore_limits', '1')
  }

  // Use passed deckId or route query (legacy support)
  const targetDeckId = deckId || route.query.deck_id
  if (targetDeckId) query.append('deck_id', targetDeckId)
  
  try {
    const { data } = await useFetch(`${API_BASE_URL}/review/due?${query.toString()}`, {
      headers: { Authorization: `Bearer ${token}` }
    }).json()

    if (data.value) {
      const newCards = data.value.cards || []
      queue.value = newCards
      
      // If adding to existing session (Review More)
      if (force && viewMode.value === 'session' && sessionStats.value.total > 0) {
          sessionStats.value.total += newCards.length
      } else {
          // Initial load
          sessionStats.value.total = newCards.length
          sessionStats.value.reviewed = 0
          sessionStats.value.correct = 0
          currentIndex.value = 0 // Reset only on fresh start
      }
      
      if (viewMode.value === 'session' && force) {
          currentIndex.value = queue.value.length - newCards.length // If appending, might need logic adjustment
          // Actually, queue replacement is simpler:
          // The backend returns a set. If we are "Reviewing More", we usually want to append.
          // But here we replaced queue.value. 
          // Let's keep it simple: "Review More" replaces the queue with new set.
          currentIndex.value = 0 
      }
      
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

// --- Actions ---

const startDailyReview = () => {
    viewMode.value = 'session'
    fetchDueCards(false, 20) // Default daily limit
}

const startDeckStudy = () => {
    if (!selectedDeckId.value) {
        toast.error('Please select a deck')
        return
    }
    viewMode.value = 'session'
    fetchDueCards(true, customLimit.value, selectedDeckId.value)
}


const revealAnswer = () => {
  // 1. Try to grab input from Front card
  const inputEl = document.querySelector('input[placeholder="Type answer..."]')
  if (inputEl) {
      userTypedAnswer.value = inputEl.value
  } else {
      userTypedAnswer.value = null
  }

  isRevealed.value = true
  
  // 2. Try to grab hidden correct answer from Back card
  nextTick(() => {
      const hiddenSpan = document.getElementById('typeans-correct')
      if (hiddenSpan) {
          correctAnswer.value = hiddenSpan.innerText
      } else {
          correctAnswer.value = null
      }
  })
}

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
      queue.value = [] 
    }

  } catch (err) {
    toast.error('Failed to save review', { description: 'Your progress was not saved.' })
  } finally {
    isSubmitting.value = false
  }
}

const handleKeydown = (e) => {
  if (viewMode.value !== 'session' || isLoading.value || !currentCard.value) return
  
  if (e.code === 'Space') {
    e.preventDefault() 
    if (!isRevealed.value) {
      revealAnswer()
    }
  }

  if (isRevealed.value && !isSubmitting.value) {
    switch(e.key) {
      case '1': submitReview(1); break;
      case '2': submitReview(2); break;
      case '3': submitReview(3); break; 
      case '4': submitReview(4); break; 
    }
  }
}

onMounted(() => {
  fetchDecks()
  // Check if routed with intent (e.g. from Deck List "Study" button)
  if (route.query.deck_id) {
      // Pre-select deck and maybe auto-start?
      // User requested "Show Menu", but "Study Now" (from Decks) usually implies immediate start.
      // Let's pre-select the Deck Mode and the Deck ID, but let user confirm to be consistent with "Menu" request?
      // OR satisfy "Exactly like Study Now in decks" -> this implies DeckList button might want immediate start.
      // But the User *also* said "Study View updates to show menu". 
      // Compromise: Pre-select Deck Mode in the UI.
      selectedMode.value = 'deck'
      selectedDeckId.value = route.query.deck_id
  }
  
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})

</script>

<template>
  <div class="absolute inset-0 z-0 bg-slate-100/50 dark:bg-black/20 pointer-events-none backdrop-blur-[2px]"></div>

  <div class="relative z-10 min-h-full flex flex-col max-w-4xl mx-auto w-full p-3 sm:p-4 lg:p-8">
    
    <!-- MENU MODE -->
    <div v-if="viewMode === 'menu'" class="flex flex-col items-center justify-center flex-1 space-y-8 animate-in fade-in zoom-in-95 duration-500">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Choose Your Session</h1>
            <p class="text-slate-500 dark:text-slate-400 text-lg">Select how you want to learn today.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-3xl">
            <!-- Daily Review Option -->
            <Card 
                @click="selectedMode = 'daily'"
                class="cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden group border-2"
                :class="selectedMode === 'daily' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-transparent hover:border-slate-200 dark:hover:border-slate-800'"
            >
                <CardHeader>
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center mb-4 shadow-sm group-hover:scale-110 transition-transform">
                        <Calendar class="w-6 h-6" />
                    </div>
                    <CardTitle class="text-xl">Daily Review</CardTitle>
                    <CardDescription>Review cards scheduled by Tsuyanki's algorithm.</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Optimized for long-term retention. Use this mode every day to keep your streak alive.
                    </p>
                </CardContent>
                <div v-if="selectedMode === 'daily'" class="absolute top-4 right-4 text-indigo-500">
                    <CheckCircle2 class="w-6 h-6" />
                </div>
            </Card>

            <!-- Deck Study Option -->
            <Card 
                @click="selectedMode = 'deck'"
                class="cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden group border-2"
                 :class="selectedMode === 'deck' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-transparent hover:border-slate-200 dark:hover:border-slate-800'"
            >
                <CardHeader>
                     <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400 flex items-center justify-center mb-4 shadow-sm group-hover:scale-110 transition-transform">
                        <Layers class="w-6 h-6" />
                    </div>
                    <CardTitle class="text-xl">Deck Study</CardTitle>
                    <CardDescription>Study a specific deck without affecting schedule.</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Best for cramming before a test or previewing a new deck.
                    </p>
                </CardContent>
                 <div v-if="selectedMode === 'deck'" class="absolute top-4 right-4 text-indigo-500">
                    <CheckCircle2 class="w-6 h-6" />
                </div>
            </Card>
        </div>

        <!-- Action Area -->
        <div class="w-full max-w-md space-y-6 bg-white/50 dark:bg-slate-900/50 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 backdrop-blur-sm shadow-sm transition-all animate-in slide-in-from-bottom-4">
            
            <div v-if="selectedMode === 'daily'" class="space-y-4">
                <div class="flex items-center justify-between text-sm text-slate-500">
                    <span>Session Goal</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100">Standard Review</span>
                </div>
                <Button size="lg" class="w-full bg-indigo-600 hover:bg-indigo-700 text-lg font-bold shadow-lg shadow-indigo-200 dark:shadow-indigo-900/50" @click="startDailyReview">
                    Start Daily Review
                </Button>
            </div>

            <div v-else class="space-y-4">
                 <div class="space-y-2">
                    <Label>Select Deck</Label>
                    <Select v-model="selectedDeckId">
                        <SelectTrigger>
                            <SelectValue placeholder="Choose a deck..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="deck in decks" :key="deck.id" :value="deck.id">
                                {{ deck.title }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                 </div>
                 <div class="space-y-2">
                     <Label>Number of Cards</Label>
                     <div class="flex items-center gap-4">
                         <Input type="number" v-model="customLimit" min="1" max="500" class="w-full" />
                         <span class="text-xs text-slate-400 whitespace-nowrap">cards</span>
                     </div>
                 </div>
                 <Button :disabled="!selectedDeckId" size="lg" class="w-full bg-indigo-600 hover:bg-indigo-700 text-lg font-bold shadow-lg shadow-indigo-200 dark:shadow-indigo-900/50" @click="startDeckStudy">
                    Start Custom Session
                </Button>
            </div>

        </div>
    </div>
    
    <!-- SESSION MODE (Existing UI wrapped) -->
    <div v-else class="flex-1 flex flex-col h-full animate-in fade-in slide-in-from-bottom-8 duration-500">
        
        <!-- Header / Progress -->
        <div class="mb-4 lg:mb-8 space-y-2 shrink-0 opacity-70 hover:opacity-100 transition-opacity duration-300 flex items-end gap-4">
          <Button variant="ghost" size="icon" class="mb-1 rounded-full hover:bg-slate-200 dark:hover:bg-slate-800" @click="viewMode = 'menu'">
              <ArrowLeft class="w-5 h-5" />
          </Button>
          <div class="flex-1 space-y-2">
             <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-300 font-medium">
                <span class="tracking-wide uppercase text-xs text-slate-400">{{ selectedMode === 'daily' ? 'Daily Review' : 'Custom Study' }}</span>
                <span>{{ sessionStats.reviewed }} / {{ sessionStats.total }}</span>
             </div>
             <Progress :model-value="progressPercentage" class="h-2 bg-slate-200 dark:bg-slate-800 [&>div]:bg-indigo-500" />
          </div>
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
               You reviewed {{ sessionStats.reviewed }} cards.
             </p>
             <div class="flex flex-col sm:flex-row justify-center gap-4 pt-8 px-4 sm:px-0 w-full">
                 <Button @click="viewMode = 'menu'" size="lg" variant="outline" class="w-full sm:w-auto order-2 sm:order-1 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">
                    Back to Menu
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
                    <Button @click="() => fetchDueCards(true, extraCardsCount, selectedDeckId)" size="lg" class="flex-1 sm:flex-initial shadow-md bg-indigo-600 hover:bg-indigo-700 text-white border-0">
                       <RotateCcw class="w-4 h-4 mr-2" /> Review More
                    </Button>
                 </div>
              </div>
          </div>

          <!-- Flashcard -->
          <Card v-else class="w-full max-w-2xl shadow-2xl border-0 ring-1 ring-slate-900/5 dark:ring-white/10 dark:bg-slate-900/90 backdrop-blur-md overflow-hidden flex flex-col min-h-[calc(100vh-160px)] sm:min-h-[400px] transition-all duration-300 hover:shadow-indigo-500/10">
            <CardHeader class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 py-3 px-4 sm:px-6">
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
  </div>
</template>

<style scoped>
/* Smooth typography */
.prose {
    font-feature-settings: "rlig" 1, "calt" 1;
}
</style>
