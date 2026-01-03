<template>
  <div class="fixed inset-0 min-h-screen w-full overflow-hidden pointer-events-none z-0">
    <!-- Light Mode Decorations -->
    <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob dark:hidden"></div>
    <div class="absolute top-0 -right-4 w-[500px] h-[500px] bg-slate-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000 dark:hidden"></div>
    <div class="absolute -bottom-32 left-20 w-[600px] h-[600px] bg-indigo-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000 dark:hidden"></div>

    <!-- Dark Mode Decorations -->
    <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-indigo-900/20 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-blob hidden dark:block"></div>
    <div class="absolute top-0 -right-4 w-[500px] h-[500px] bg-slate-800/30 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-blob animation-delay-2000 hidden dark:block"></div>
    <div class="absolute -bottom-32 left-20 w-[600px] h-[600px] bg-indigo-950/30 rounded-full mix-blend-screen filter blur-3xl opacity-20 animate-blob animation-delay-4000 hidden dark:block"></div>
  </div>
</template>

<style scoped>
.animate-blob {
  animation: blob 10s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
.animation-delay-4000 {
  animation-delay: 4s;
}

@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
