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

const API_BASE = 'http://localhost:8000/api/v1'
const token = localStorage.getItem('auth_token')
const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' }

// State
const loading = ref(true)
const dueCount = ref(0)
const reviewedToday = ref(0)
const streak = ref(0) // Mocked for now as backend might not return it yet fully
const recentActivity = ref([4, 10, 2, 15, 8, 12, 5]) // Mocked last 7 days

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
      if (statsData.value.recent_activity) {
          recentActivity.value = statsData.value.recent_activity
      }
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
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Dashboard</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm sm:text-base">Welcome back! Let's make some progress today.</p>
      </div>
      <div class="flex items-center gap-2">
         <!-- Date Display or similar could go here -->
      </div>
    </div>

    <!-- Hero / Quick Action Section -->
    <div class="grid gap-4 md:gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Main Hero Card -->
        <Card class="col-span-full lg:col-span-2 overflow-hidden border-none shadow-lg relative bg-gradient-to-br from-indigo-600 to-purple-700 text-white">
            <div class="absolute -top-10 -right-10 md:top-0 md:right-0 opacity-10 pointer-events-none">
                <Zap class="w-40 h-40 md:w-64 md:h-64 rotate-12" />
            </div>
            <CardContent class="relative z-10 p-6 sm:p-8 flex flex-col items-start justify-center h-full">
                <div class="flex items-center gap-2 mb-4">
                    <Badge variant="secondary" class="bg-white/20 hover:bg-white/30 text-white border-0">
                        <Flame class="w-3 h-3 mr-1 text-orange-300" /> {{ streak }} Day Streak
                    </Badge>
                </div>
                
                <h2 class="text-3xl font-bold mb-2">Ready to crush your goals?</h2>
                <p class="text-indigo-100 mb-8 max-w-lg text-lg">
                    You have <span class="font-bold text-white">{{ dueCount }} cards</span> waiting for review. 
                    Consistency is the key to mastery!
                </p>

                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <Button @click="router.push('/study')" size="lg" class="w-full sm:w-auto bg-white text-indigo-600 hover:bg-indigo-50 font-bold border-0 shadow-md">
                        <Play class="w-5 h-5 mr-2 fill-current" />
                        Start Review Session
                    </Button>
                    <Button variant="outline" class="w-full sm:w-auto bg-indigo-700/50 border-white/20 text-white hover:bg-indigo-700/70 hover:text-white">
                        Browse Decks
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Quick Stats / Secondary Actions -->
        <div class="flex flex-col gap-4">
             <!-- Create New -->
            <Card @click="router.push('/decks/create')" class="flex-1 bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-900/50 hover:shadow-md transition-shadow cursor-pointer group">
                <CardContent class="flex items-center p-6 h-full">
                    <div class="h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                        <PlusCircle class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-emerald-900 dark:text-emerald-100">Create New Deck</h3>
                        <p class="text-sm text-emerald-600/80 dark:text-emerald-400/80">Add your own cards</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Search -->
             <Card @click="router.push('/decks')" class="flex-1 bg-sky-50 dark:bg-sky-950/30 border-sky-100 dark:border-sky-900/50 hover:shadow-md transition-shadow cursor-pointer group">
                <CardContent class="flex items-center p-6 h-full">
                    <div class="h-12 w-12 rounded-full bg-sky-100 dark:bg-sky-900 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                        <Search class="h-6 w-6 text-sky-600 dark:text-sky-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sky-900 dark:text-sky-100">Browse Decks</h3>
                        <p class="text-sm text-sky-600/80 dark:text-sky-400/80">View your collection</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>

    <!-- Stats Row Detailed -->
    <div class="grid gap-4 md:grid-cols-3">
      <!-- Cards Due -->
      <Card class="border-l-4 border-l-rose-500 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-slate-500">
            Reviews Due
          </CardTitle>
          <div class="h-8 w-8 rounded-full bg-rose-50 flex items-center justify-center">
            <Clock class="h-4 w-4 text-rose-500" />
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="loading">
             <Skeleton class="h-8 w-20 mb-1" />
          </div>
          <div v-else>
            <div class="text-3xl font-bold text-slate-900 dark:text-slate-50">{{ dueCount }}</div>
            <p class="text-xs text-rose-500 font-medium mt-1">
              Needs attention
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Reviewed Today -->
      <Card class="border-l-4 border-l-blue-500 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-slate-500">
            Studied Today
          </CardTitle>
           <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center">
            <CheckCircle2 class="h-4 w-4 text-blue-500" />
          </div>
        </CardHeader>
        <CardContent>
           <div v-if="loading">
             <Skeleton class="h-8 w-20 mb-1" />
          </div>
          <div v-else>
            <div class="text-3xl font-bold text-slate-900 dark:text-slate-50">{{ reviewedToday }}</div>
            <p class="text-xs text-blue-500 font-medium mt-1">
              +15% from yesterday
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Mastery -->
      <Card class="border-l-4 border-l-amber-500 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium text-slate-500">
            Total Mastery
          </CardTitle>
           <div class="h-8 w-8 rounded-full bg-amber-50 flex items-center justify-center">
            <Trophy class="h-4 w-4 text-amber-500" />
          </div>
        </CardHeader>
        <CardContent>
           <div v-if="loading">
             <Skeleton class="h-8 w-20 mb-1" />
          </div>
          <div v-else>
            <div class="text-3xl font-bold text-slate-900 dark:text-slate-50">85%</div>
            <p class="text-xs text-amber-500 font-medium mt-1">
              Top 10% of learners
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
