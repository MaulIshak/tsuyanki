<script setup>
import { Toaster } from '@/components/ui/sonner'
</script>

<template>
  <main class="min-h-screen w-full bg-background antialiased">
    <RouterView />
    <Toaster />
  </main>
</template>

<style>
body, html, #app {
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}
</style>