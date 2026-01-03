<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useFetch, useDark } from '@vueuse/core'
import { toast } from 'vue-sonner'
import { Loader2, User, BookOpen, Monitor, AlertTriangle, Save, Sun, Moon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
  <div class="space-y-6">
    <div>
      <h3 class="text-lg font-medium">Settings</h3>
      <p class="text-sm text-slate-500">
        Manage your account settings and set e-mail preferences.
      </p>
    </div>
    <Separator />
    
    <div v-if="isLoading" class="space-y-4">
        <div class="h-64 w-full bg-slate-100 dark:bg-slate-800 animate-pulse rounded-lg"></div>
    </div>

    <Tabs v-else defaultValue="profile" class="space-y-4">
      <TabsList>
        <TabsTrigger value="profile" class="gap-2"><User class="w-4 h-4"/> Profile</TabsTrigger>
        <TabsTrigger value="learning" class="gap-2"><BookOpen class="w-4 h-4"/> Learning</TabsTrigger>
        <TabsTrigger value="appearance" class="gap-2"><Monitor class="w-4 h-4"/> Appearance</TabsTrigger>
        <TabsTrigger value="account" class="gap-2 text-red-600"><AlertTriangle class="w-4 h-4"/> Danger Zone</TabsTrigger>
      </TabsList>
      
      <!-- Profile Tab -->
      <TabsContent value="profile" class="space-y-4">
          <Card>
              <CardHeader>
                  <CardTitle>Profile Information</CardTitle>
                  <CardDescription>Update your account profile information.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                  <div class="flex items-center gap-4 mb-4" v-if="form.avatar || form.google_id">
                      <div class="relative">
                        <img v-if="form.avatar" :src="form.avatar" alt="Avatar" class="w-16 h-16 rounded-full border-2 border-slate-200 dark:border-slate-700">
                        <div v-else class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl">
                            {{ form.name.charAt(0).toUpperCase() }}
                        </div>
                         <div v-if="form.google_id" class="absolute -bottom-1 -right-1 bg-white dark:bg-slate-900 rounded-full p-1 border shadow-sm" title="Connected with Google">
                             <svg class="w-4 h-4" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="google" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512"><path fill="currentColor" d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"></path></svg>
                        </div>
                      </div>
                      <div>
                          <p class="font-medium" v-if="form.google_id">Connected with Google</p>
                          <p class="text-xs text-muted-foreground" v-if="form.google_id">Your account is linked to your Google account.</p>
                      </div>
                  </div>

                  <div class="grid gap-2">
                       <Label htmlFor="name">Display Name</Label>
                       <Input id="name" v-model="form.name" />
                  </div>
                  <div class="grid gap-2">
                       <Label htmlFor="email">Email Address</Label>
                       <Input id="email" v-model="form.email" type="email" :disabled="!!form.google_id" />
                       <p class="text-[10px] text-muted-foreground" v-if="form.google_id">Email managed by Google.</p>
                  </div>
              </CardContent>
              <CardFooter class="border-t px-6 py-4">
                  <Button @click="saveSettings" :disabled="isSaving">
                      <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                      Save Changes
                  </Button>
              </CardFooter>
          </Card>
      </TabsContent>

      <!-- Learning Tab -->
      <TabsContent value="learning" class="space-y-4">
           <Card>
              <CardHeader>
                  <CardTitle>Learning Preferences</CardTitle>
                  <CardDescription>Customize your daily study goals.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                  <div class="flex items-center justify-between rounded-lg border p-4">
                       <div class="space-y-0.5">
                            <Label class="text-base">Daily New Cards Limit</Label>
                            <p class="text-sm text-slate-500">Maximum number of new cards to introduce per day.</p>
                       </div>
                       <div class="w-[100px]">
                           <Input type="number" v-model="form.preferences.daily_goal" />
                       </div>
                  </div>
              </CardContent>
              <CardFooter class="border-t px-6 py-4">
                  <Button @click="saveSettings" :disabled="isSaving">
                      <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                      Save Preferences
                  </Button>
              </CardFooter>
          </Card>
      </TabsContent>

      <!-- Appearance Tab -->
      <TabsContent value="appearance" class="space-y-4">
          <Card>
              <CardHeader>
                  <CardTitle>Appearance</CardTitle>
                  <CardDescription>Select your preferred theme.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                   <div class="space-y-2">
                       <Label>Theme</Label>
                       <div class="grid grid-cols-3 gap-3 max-w-md">
                           <div @click="form.preferences.theme = 'light'" 
                                :class="['flex items-center justify-center gap-2 cursor-pointer rounded-md border-2 p-3 text-sm font-medium transition-colors', form.preferences.theme === 'light' ? 'border-primary bg-background text-primary' : 'border-muted bg-muted text-muted-foreground hover:bg-muted/80']">
                               <Sun class="w-4 h-4" />
                               Light
                           </div>
                           <div @click="form.preferences.theme = 'dark'" 
                                :class="['flex items-center justify-center gap-2 cursor-pointer rounded-md border-2 p-3 text-sm font-medium transition-colors', form.preferences.theme === 'dark' ? 'border-primary bg-background text-primary' : 'border-muted bg-muted text-muted-foreground hover:bg-muted/80']">
                               <Moon class="w-4 h-4" />
                               Dark
                           </div>
                            <div @click="form.preferences.theme = 'system'" 
                                :class="['flex items-center justify-center gap-2 cursor-pointer rounded-md border-2 p-3 text-sm font-medium transition-colors', form.preferences.theme === 'system' ? 'border-primary bg-background text-primary' : 'border-muted bg-muted text-muted-foreground hover:bg-muted/80']">
                               <Monitor class="w-4 h-4" />
                               System
                           </div>
                       </div>
                       <p class="text-xs text-slate-500 mt-2">
                          Note: The top-right theme toggle overrides this temporarily. This setting is for your default preference.
                       </p>
                   </div>
              </CardContent>
               <CardFooter class="border-t px-6 py-4">
                  <Button @click="saveSettings" :disabled="isSaving">
                      <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                      Save Preferences
                  </Button>
              </CardFooter>
          </Card>
      </TabsContent>

      <!-- Account Tab (Danger Zone) -->
      <TabsContent value="account" class="space-y-4">
          <Card class="border-red-200 dark:border-red-900/50">
              <CardHeader>
                  <CardTitle class="text-red-600 dark:text-red-500">Danger Zone</CardTitle>
                  <CardDescription>Irreversible actions for your account.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                  <div class="rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-900/20">
                      <div class="flex items-start gap-4">
                          <AlertTriangle class="h-5 w-5 text-red-600 mt-0.5" />
                          <div class="space-y-1">
                              <h4 class="font-medium text-red-900 dark:text-red-200">Delete Account</h4>
                              <p class="text-sm text-red-700 dark:text-red-300">
                                  Once you delete your account, there is no going back. All your decks, cards, and learning progress will be permanently removed.
                              </p>
                          </div>
                      </div>
                  </div>
              </CardContent>
              <CardFooter class="border-t border-red-200 bg-red-50/50 px-6 py-4 dark:border-red-900/50 dark:bg-red-900/10">
                  <AlertDialog>
                    <AlertDialogTrigger asChild>
                        <Button variant="destructive" :disabled="isDeleting">
                            <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                            Delete Account Permanently
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
              </CardFooter>
          </Card>
      </TabsContent>

    </Tabs>
  </div>
</template>
