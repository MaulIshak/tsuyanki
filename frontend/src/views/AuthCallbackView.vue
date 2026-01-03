<script setup>
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { toast } from 'vue-sonner'

const router = useRouter()
const route = useRoute()

onMounted(async () => {
  const token = route.query.token
  // We don't get the user object in the query param (it would be huge/unsafe to put in URL).
  // Ideally, we get the token, store it, then fetch /auth/me to get the user.

  if (token) {
    localStorage.setItem('auth_token', token)
    
    try {
        // Fetch user data
        const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
        const response = await fetch(`${API_BASE_URL}/auth/me`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        
        if (response.ok) {
            const userData = await response.json()
            localStorage.setItem('user_data', JSON.stringify(userData))
            toast.success('Login Successful', { description: 'Welcome back via Google.' })
            router.push('/dashboard')
        } else {
            throw new Error('Failed to fetch user profile')
        }
    } catch (e) {
        toast.error('Login Failed', { description: 'Could not retrieve user data.' })
        router.push('/login')
    }
    
  } else {
    toast.error('Login Failed', { description: 'No token received from Google.' })
    router.push('/login')
  }
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center">
    <div class="flex flex-col items-center space-y-4">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      <p class="text-muted-foreground">Completing secure login...</p>
    </div>
  </div>
</template>
