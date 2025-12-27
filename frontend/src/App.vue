<script setup>
import { Toaster } from '@/components/ui/sonner'
</script>

<template>
  <div class="h-full w-full bg-background antialiased">
    <RouterView />
    <Toaster />
  </div>
</template>

<style>
body, html, #app {
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;

}
</style>