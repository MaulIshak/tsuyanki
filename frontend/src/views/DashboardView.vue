<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Badge } from '@/components/ui/badge'
import { 
  Users, Play, CalendarCheck, Flame, 
  Zap, PlusCircle, Search, Clock, CheckCircle2, Trophy, TrendingUp 
} from 'lucide-vue-next'
import { useFetch } from '@vueuse/core'
import { useRouter } from 'vue-router'

const router = useRouter()
// ...

interface Stats {
  period: string
  reviews_completed: number
}

interface DueCards {
  cards: any[]
  summary: {
    total: number
  }
}

const API_BASE = import.meta.env.VITE_API_BASE_URL
const token = localStorage.getItem('auth_token')
const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' }

// State
const loading = ref(true)
const dueCount = ref(0)
const reviewedToday = ref(0)
const mastery = ref(0)
const streak = ref(0)
const recentActivity = ref([])
const recentDecks = ref([])

// Fetch Data
const fetchDashboardData = async () => {
  loading.value = true
  try {
    // 1. Due Cards
    const { data: dueData } = await useFetch(`${API_BASE}/review/due?limit=1`, { headers }).json<DueCards>()
    if (dueData.value) {
      dueCount.value = dueData.value.summary.total
    }

    // 2. Stats
    const { data: statsData } = await useFetch(`${API_BASE}/review/stats`, { headers }).json()
    if (statsData.value) {
      reviewedToday.value = statsData.value.reviews_completed
      streak.value = statsData.value.streak
      mastery.value = statsData.value.total_mastery
      if (statsData.value.recent_activity) {
          recentActivity.value = statsData.value.recent_activity
      }
    }

    // 3. Recent Decks
    const { data: decksData } = await useFetch(`${API_BASE}/decks`, { headers }).json()
    if (decksData.value) {
        recentDecks.value = decksData.value.data.slice(0, 3) 
    }
  } catch (error) {
    console.error('Failed to fetch dashboard data', error)
  } finally {
    loading.value = false
  }
}

const startReview = () => {
  // Navigate to review page (to be implemented)
  console.log('Start Review')
}

onMounted(() => {
  fetchDashboardData()
})

const maxActivity = computed(() => Math.max(...recentActivity.value, 1))

</script>

<template>
  <div class="flex flex-col gap-4 md:gap-6 pb-20 md:pb-0">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Dashboard</h1>
        <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-base">Welcome back! Let's make some progress today.</p>
      </div>
      <div class="flex items-center gap-2">
         <!-- Date Display or similar could go here -->
      </div>
    </div>

    <!-- Hero / Quick Action Section -->
    <div class="grid gap-4 md:gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Main Hero Card -->
        <Card class="col-span-full lg:col-span-2 overflow-hidden border-none shadow-lg relative bg-gradient-to-br from-indigo-500 to-indigo-600 text-white group">
            <div class="absolute -top-10 -right-10 md:top-0 md:right-0 opacity-10 pointer-events-none group-hover:scale-105 transition-transform duration-700">
                <Zap class="w-40 h-40 md:w-64 md:h-64 rotate-12" />
            </div>
            <!-- Decorative soft blobs for depth inside the card -->
            <div class="absolute top-1/2 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>

            <CardContent class="relative z-10 p-5 sm:p-8 flex flex-col items-start justify-center h-full">
                <div class="flex items-center gap-2 mb-3">
                    <Badge variant="secondary" class="bg-white/20 hover:bg-white/30 text-white border-0 backdrop-blur-sm">
                        <Flame class="w-3 h-3 mr-1 text-orange-200" /> {{ streak }} Day Streak
                    </Badge>
                </div>
                
                <h2 class="text-2xl sm:text-3xl font-bold mb-2 tracking-tight">Ready to flow?</h2>
                <p class="text-indigo-50 mb-6 max-w-lg text-base sm:text-lg leading-relaxed">
                    You have <span class="font-bold text-white">{{ dueCount }} cards</span> waiting for review. 
                    Consistency is the path to mastery.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <Button @click="router.push('/study')" size="lg" class="w-full sm:w-auto bg-white text-indigo-600 hover:bg-indigo-50 font-bold border-0 shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">
                        <Play class="w-5 h-5 mr-2 fill-current" />
                        Start Review Session
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Recent Decks / Secondary Actions -->
        <div class="flex flex-col gap-4">
             <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-slate-100">Your Decks</h3>
                <Button variant="ghost" size="sm" @click="router.push('/decks')">View All</Button>
             </div>
             
             <div v-if="loading" class="space-y-3">
                 <Skeleton class="h-16 w-full rounded-lg" />
                 <Skeleton class="h-16 w-full rounded-lg" />
             </div>
             
             <div v-else-if="recentDecks.length === 0" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-slate-400 bg-slate-50/50 dark:bg-slate-900/50">
                 <PlusCircle class="h-8 w-8 mb-2 opacity-50" />
                 <p class="text-sm">No decks yet</p>
                 <Button variant="link" @click="router.push('/decks/create')">Create one</Button>
             </div>

             <div v-else class="space-y-3">
                 <Card 
                    v-for="deck in recentDecks" 
                    :key="deck.id" 
                    @click="router.push(`/decks/${deck.id}`)"
                    class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all cursor-pointer shadow-sm hover:shadow-md hover:-translate-y-0.5 group"
                >
                    <CardContent class="p-4 flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-slate-900 dark:text-slate-100 line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ deck.title }}</h4>
                            <p class="text-xs text-slate-500 line-clamp-1">{{ deck.description || 'No description' }}</p>
                        </div>
                        <Play class="h-4 w-4 text-indigo-500 opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-1" />
                    </CardContent>
                 </Card>
             </div>
             
             <Button variant="outline" class="w-full border-dashed border-slate-300 dark:border-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-slate-600 dark:text-slate-400" @click="router.push('/decks/create')">
                <PlusCircle class="w-4 h-4 mr-2" /> Create New Deck
             </Button>
        </div>
    </div>


    <!-- Stats Row Detailed -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
      <!-- Cards Due -->
      <Card class="border-none shadow-md bg-white dark:bg-slate-800/50 backdrop-blur-sm overflow-hidden relative group hover:shadow-lg transition-all duration-300">
        <div class="absolute right-0 top-0 opacity-5 transform translate-x-1/4 -translate-y-1/4 group-hover:scale-110 transition-transform duration-500 text-indigo-500">
             <Clock class="w-32 h-32" />
        </div>
        <CardHeader class="flex flex-row items-center justify-between pb-2 relative z-10">
          <CardTitle class="text-sm font-medium text-slate-500 dark:text-slate-400">
            Reviews Due
          </CardTitle>
          <div class="h-8 w-8 rounded-full bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center">
            <Clock class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
          </div>
        </CardHeader>
        <CardContent class="relative z-10">
          <div v-if="loading">
             <Skeleton class="h-8 w-20 mb-1" />
          </div>
          <div v-else>
            <div class="text-3xl font-bold text-slate-900 dark:text-slate-50">{{ dueCount }}</div>
            <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1">
              <span v-if="dueCount > 0" class="flex h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
              {{ dueCount > 0 ? 'Action required' : 'All caught up!' }}
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Reviewed Today -->
      <Card class="border-none shadow-md bg-white dark:bg-slate-800/50 backdrop-blur-sm overflow-hidden relative group hover:shadow-lg transition-all duration-300">
        <div class="absolute right-0 top-0 opacity-5 transform translate-x-1/4 -translate-y-1/4 group-hover:scale-110 transition-transform duration-500 text-emerald-500">
             <CheckCircle2 class="w-32 h-32" />
        </div>
        <CardHeader class="flex flex-row items-center justify-between pb-2 relative z-10">
          <CardTitle class="text-sm font-medium text-slate-500 dark:text-slate-400">
            Studied Today
          </CardTitle>
           <div class="h-8 w-8 rounded-full bg-emerald-50 dark:bg-emerald-900/50 flex items-center justify-center">
            <CheckCircle2 class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
          </div>
        </CardHeader>
        <CardContent class="relative z-10">
           <div v-if="loading">
             <Skeleton class="h-8 w-20 mb-1" />
          </div>
          <div v-else>
            <div class="text-3xl font-bold text-slate-900 dark:text-slate-50">{{ reviewedToday }}</div>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-1">
               {{ Math.round((reviewedToday / 20) * 100) }}% of daily goal
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Mastery -->
      <Card class="border-none shadow-md bg-white dark:bg-slate-800/50 backdrop-blur-sm overflow-hidden relative group hover:shadow-lg transition-all duration-300">
        <div class="absolute right-0 top-0 opacity-5 transform translate-x-1/4 -translate-y-1/4 group-hover:scale-110 transition-transform duration-500 text-amber-500">
             <Trophy class="w-32 h-32" />
        </div>
        <CardHeader class="flex flex-row items-center justify-between pb-2 relative z-10">
          <CardTitle class="text-sm font-medium text-slate-500 dark:text-slate-400">
            Total Mastery
          </CardTitle>
           <div class="h-8 w-8 rounded-full bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center">
            <Trophy class="h-4 w-4 text-amber-600 dark:text-amber-400" />
          </div>
        </CardHeader>
        <CardContent class="relative z-10">
           <div v-if="loading">
             <Skeleton class="h-8 w-20 mb-1" />
          </div>
          <div v-else>
            <div class="text-3xl font-bold text-slate-900 dark:text-slate-50">{{ mastery }}%</div>
            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium mt-1">
              Of cards matured
            </p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Chart Section -->
    <Card>
        <CardHeader class="flex flex-row items-center justify-between">
            <div class="space-y-1">
                <CardTitle>Learning Activity</CardTitle>
                <p class="text-sm text-slate-500">Your practice history over the last 7 days.</p>
            </div>
            <Badge variant="outline" class="gap-1">
                <TrendingUp class="h-3 w-3" /> Trending Up
            </Badge>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="h-[200px] flex items-end justify-between gap-2 p-4">
                <Skeleton v-for="i in 7" :key="i" class="w-full" :style="{ height: Math.random() * 100 + '%' }" />
            </div>
            <div v-else class="h-[240px] flex items-end justify-between gap-6 pt-8 pb-2 px-2">
                <div 
                v-for="(val, index) in recentActivity" 
                :key="index"
                class="w-full relative group flex flex-col justify-end h-full"
                >
                    <div 
                        class="w-full bg-indigo-100 dark:bg-indigo-900/40 rounded-t-lg relative transition-all duration-300 group-hover:bg-indigo-500 group-hover:scale-y-105 origin-bottom"
                        :style="{ height: `${(val / maxActivity) * 100}%`, minHeight: '4px' }"
                    ></div>
                    <div class="opacity-0 group-hover:opacity-100 absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs px-2 py-1 rounded shadow-lg transition-opacity mb-2 whitespace-nowrap z-10">
                        {{ val }} Cards
                    </div>
                </div>
            </div>
            <div class="flex justify-between px-2 text-xs font-medium text-slate-400 uppercase tracking-wide mt-4">
                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
            </div>
        </CardContent>
    </Card>

  </div>
</template>
