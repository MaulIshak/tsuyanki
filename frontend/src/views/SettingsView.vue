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
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
const isLoading = ref(true)
const isSaving = ref(false)
const isDark = useDark()

const form = reactive({
    name: '',
    email: '',
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
        <!-- <TabsTrigger value="account" class="gap-2 text-red-600"><AlertTriangle class="w-4 h-4"/> Danger Zone</TabsTrigger> -->
      </TabsList>
      
      <!-- Profile Tab -->
      <TabsContent value="profile" class="space-y-4">
          <Card>
              <CardHeader>
                  <CardTitle>Profile Information</CardTitle>
                  <CardDescription>Update your account profile information and email address.</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                  <div class="grid gap-2">
                       <Label htmlFor="name">Display Name</Label>
                       <Input id="name" v-model="form.name" />
                  </div>
                  <div class="grid gap-2">
                       <Label htmlFor="email">Email Address</Label>
                       <Input id="email" v-model="form.email" type="email" />
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

    </Tabs>
  </div>
</template>
