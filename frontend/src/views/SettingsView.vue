<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useFetch, useDark } from '@vueuse/core'
import { toast } from 'vue-sonner'
import { Loader2, User, BookOpen, Monitor, AlertTriangle, Save, Sun, Moon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'
import { Switch } from '@/components/ui/switch'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
const isLoading = ref(true)
const isSaving = ref(false)
const isDeleting = ref(false)
const isDark = useDark()
const router = useRouter()

const form = reactive({
    name: '',
    email: '',
    avatar: '',
    google_id: null,
    preferences: {
        daily_goal: '20',
        theme: 'system',
        marketing_emails: false
    }
})

// Fetch current user data
const fetchUser = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        const { data } = await useFetch(`${API_BASE_URL}/auth/me`, {
            headers: { Authorization: `Bearer ${token}` }
        }).json()
        
        if (data.value) {
            form.name = data.value.name
            form.email = data.value.email
            form.avatar = data.value.avatar
            form.google_id = data.value.google_id
            if (data.value.preferences) {
                // Merge preferences carefully
                form.preferences = { ...form.preferences, ...data.value.preferences }
            }
        }
    } catch (e) {
        toast.error('Failed to load profile')
    } finally {
        isLoading.value = false
    }
}

const saveSettings = async () => {
    isSaving.value = true
    const token = localStorage.getItem('auth_token')
    
    try {
        const { error, data } = await useFetch(`${API_BASE_URL}/auth/profile`, {
            method: 'PUT',
            headers: { 
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(form)
        }).json()

        if (error.value) throw new Error('Update failed')

        toast.success('Settings updated')
        
        // Apply theme immediately
        if (form.preferences.theme === 'dark') {
            isDark.value = true
        } else if (form.preferences.theme === 'light') {
            isDark.value = false
        } else {
             // System: remove override and let system decide (requires reload or simple assumption)
             // For now, removing the storage key allows useDark to re-eval?
             // actually useDark reads media query if no storage.
             localStorage.removeItem('vueuse-color-scheme')
             // Force re-eval?
             isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
        }

        // Update local storage user data just in case
        if (data.value?.user) {
             localStorage.setItem('user_data', JSON.stringify(data.value.user))
        }
    } catch (e) {
        toast.error('Failed to save settings')
    } finally {
        isSaving.value = false
    }
}

const handleDeleteAccount = async () => {
    isDeleting.value = true
    const token = localStorage.getItem('auth_token')
    
    try {
        const { error } = await useFetch(`${API_BASE_URL}/auth/user`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` }
        }).json()

        if (error.value) throw new Error('Deletion failed')

        toast.success('Account deleted successfully')
        
        // Clear local storage
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user_data')
        
        // Redirect to login
        router.push('/login')
    } catch (e) {
        toast.error('Failed to delete account')
    } finally {
        isDeleting.value = false
    }
}

onMounted(() => {
    fetchUser()
})
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-8 pb-12">
    <div>
      <h3 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Settings</h3>
      <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg">
        Manage your account settings and preferences.
      </p>
    </div>
    
    <div v-if="isLoading" class="space-y-6">
        <Skeleton class="h-64 w-full rounded-2xl" />
        <Skeleton class="h-48 w-full rounded-2xl" />
    </div>

    <div v-else class="grid gap-8">
      
      <!-- Profile Section -->
      <section class="space-y-4">
        <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-semibold uppercase tracking-wider text-xs ml-1">
            <User class="w-4 h-4" /> Profile
        </div>
        <Card class="border-indigo-100 dark:border-indigo-900/50 shadow-sm overflow-hidden">
            <CardContent class="p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8">
                <div class="flex flex-col md:flex-row gap-8 items-start">
                    <div class="flex-1 space-y-4 w-full">
                        <div class="grid gap-2">
                             <Label htmlFor="name">Display Name</Label>
                             <Input id="name" v-model="form.name" class="h-11 bg-slate-50 dark:bg-slate-900" />
                        </div>
                        <div class="grid gap-2">
                             <Label htmlFor="email">Email Address</Label>
                             <Input id="email" v-model="form.email" type="email" :disabled="!!form.google_id" class="h-11 bg-slate-50 dark:bg-slate-900" />
                             <p class="text-[10px] text-muted-foreground" v-if="form.google_id">Email managed by Google.</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-4 p-6 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 w-full md:w-auto shrink-0">
                        <div class="relative">
                          <img v-if="form.avatar" :src="form.avatar" alt="Avatar" class="w-24 h-24 rounded-full border-4 border-white dark:border-slate-800 shadow-md object-cover">
                          <div v-else class="w-24 h-24 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-3xl border-4 border-white dark:border-slate-800 shadow-md">
                              {{ form.name?.charAt(0).toUpperCase() }}
                          </div>
                           <div v-if="form.google_id" class="absolute -bottom-1 -right-1 bg-white dark:bg-slate-800 rounded-full p-1.5 border dark:border-slate-700 shadow-sm" title="Connected with Google">
                               <svg class="w-5 h-5" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="google" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512"><path fill="currentColor" d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"></path></svg>
                          </div>
                        </div>
                        <div class="text-center">
                            <p class="font-medium text-sm" v-if="form.google_id">Google Account</p>
                            <p class="text-[10px] text-muted-foreground" v-if="form.google_id">Linked</p>
                        </div>
                    </div>
                </div>
            </CardContent>
             <CardFooter class="border-t bg-slate-50/50 dark:bg-slate-900/50 px-6 py-4 flex justify-end">
                <Button @click="saveSettings" :disabled="isSaving" class="bg-indigo-600 hover:bg-indigo-700 text-white min-w-[120px]">
                    <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                    Save Changes
                </Button>
            </CardFooter>
        </Card>
      </section>

      <!-- Preferences Section (Learning + Appearance) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          
          <section class="space-y-4">
            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-semibold uppercase tracking-wider text-xs ml-1">
                <BookOpen class="w-4 h-4" /> Learning
            </div>
            <Card class="flex-1 h-full shadow-sm">
                <CardContent class="p-4 sm:p-6 space-y-6">
                    <div class="space-y-4">
                        <Label class="text-base font-medium">Daily New Cards</Label>
                        <p class="text-sm text-slate-500 dark:text-slate-400">How many new cards do you want to learn each day?</p>
                         <div class="flex items-center gap-4">
                             <Input type="number" v-model="form.preferences.daily_goal" class="w-24 text-center text-lg font-bold" />
                             <span class="text-sm text-slate-500">cards / day</span>
                         </div>
                    </div>
                </CardContent>
                 <CardFooter class="border-t bg-slate-50/50 dark:bg-slate-900/50 px-6 py-4 flex justify-end">
                    <Button variant="ghost" size="sm" @click="saveSettings" :disabled="isSaving" class="text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                        Update
                    </Button>
                 </CardFooter>
            </Card>
          </section>

          <section class="space-y-4">
            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-semibold uppercase tracking-wider text-xs ml-1">
                <Monitor class="w-4 h-4" /> Appearance
            </div>
             <Card class="flex-1 h-full shadow-sm">
                <CardContent class="p-4 sm:p-6 space-y-6">
                     <div class="space-y-4">
                        <Label class="text-base font-medium">Theme Preference</Label>
                         <p class="text-sm text-slate-500 dark:text-slate-400">Choose how Tsuyanki looks to you.</p>
                         <div class="grid grid-cols-3 gap-3">
                            <div @click="form.preferences.theme = 'light'" 
                                :class="['flex flex-col items-center justify-center gap-2 cursor-pointer rounded-xl border-2 p-4 text-sm font-medium transition-all hover:scale-[1.02]', form.preferences.theme === 'light' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300' : 'border-slate-200 bg-white dark:bg-slate-950 dark:border-slate-800 text-slate-600 hover:border-indigo-200']">
                                <Sun class="w-6 h-6" />
                                Light
                            </div>
                            <div @click="form.preferences.theme = 'dark'" 
                                :class="['flex flex-col items-center justify-center gap-2 cursor-pointer rounded-xl border-2 p-4 text-sm font-medium transition-all hover:scale-[1.02]', form.preferences.theme === 'dark' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300' : 'border-slate-200 bg-white dark:bg-slate-950 dark:border-slate-800 text-slate-600 hover:border-indigo-200']">
                                <Moon class="w-6 h-6" />
                                Dark
                            </div>
                             <div @click="form.preferences.theme = 'system'" 
                                :class="['flex flex-col items-center justify-center gap-2 cursor-pointer rounded-xl border-2 p-4 text-sm font-medium transition-all hover:scale-[1.02]', form.preferences.theme === 'system' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300' : 'border-slate-200 bg-white dark:bg-slate-950 dark:border-slate-800 text-slate-600 hover:border-indigo-200']">
                                <Monitor class="w-6 h-6" />
                                Auto
                            </div>
                        </div>
                    </div>
                </CardContent>
                 <CardFooter class="border-t bg-slate-50/50 dark:bg-slate-900/50 px-6 py-4 flex justify-end">
                     <Button variant="ghost" size="sm" @click="saveSettings" :disabled="isSaving" class="text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                        Update
                    </Button>
                </CardFooter>
            </Card>
          </section>

      </div>

      <!-- Danger Zone -->
       <section class="space-y-4 pt-8">
         <div class="flex items-center gap-2 text-red-600 dark:text-red-400 font-semibold uppercase tracking-wider text-xs ml-1">
            <AlertTriangle class="w-4 h-4" /> Danger Zone
        </div>
        <Card class="border-red-100 bg-red-50/30 dark:border-red-900/30 dark:bg-red-900/10 shadow-sm border-dashed">
            <CardContent class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h4 class="font-medium text-red-900 dark:text-red-200">Delete Account</h4>
                    <p class="text-sm text-red-700/80 dark:text-red-300/70 max-w-lg">
                        Permanently remove your account and all of its contents from the Tsuyanki servers. This action is not reversible, so please continue with caution.
                    </p>
                </div>
                <AlertDialog>
                <AlertDialogTrigger asChild>
                    <Button variant="destructive" :disabled="isDeleting" class="shrink-0 bg-white text-red-600 border border-red-200 hover:bg-red-50 hover:border-red-300 dark:bg-red-950 dark:text-red-400 dark:border-red-900 dark:hover:bg-red-900 shadow-sm">
                        <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                        Delete Account
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently delete your account
                            and remove your data from our servers.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction @click="handleDeleteAccount" class="bg-red-600 hover:bg-red-700 text-white">
                            Continue
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
                </AlertDialog>
            </CardContent>
        </Card>
      </section>

    </div>
  </div>
</template>
