<script setup>
import { RouterLink } from 'vue-router'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import BackgroundDecorations from '@/components/layout/BackgroundDecorations.vue'
import {
  ArrowRight,
  Brain,
  Clock,
  Layers,
  Zap,
  CheckCircle2,
  BookOpen,
  Repeat,
  Smile,
  Globe
} from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans selection:bg-indigo-100 dark:selection:bg-indigo-900 overflow-x-hidden">
    <!-- Background Elements -->
    <BackgroundDecorations />
    
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-white/50 dark:bg-slate-950/50 backdrop-blur-md border-b border-slate-200/50 dark:border-slate-800/50 transition-all duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
           <img src="/logo.png" alt="Tsuyanki" class="w-8 h-8 object-contain" />
           <span class="font-bold text-xl tracking-tight text-slate-900 dark:text-white">Tsuyanki</span>
        </div>
        
        <div class="flex items-center gap-4">
          <Button variant="ghost" as-child class="hidden sm:inline-flex text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-white">
            <RouterLink to="/login">Log in</RouterLink>
          </Button>
          <Button as-child class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/20 rounded-full px-6">
            <RouterLink to="/register">Get Started</RouterLink>
          </Button>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center pt-32 pb-16 md:pt-20 md:pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center relative z-10">
        <div class="space-y-8 text-center lg:text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-sm font-medium border border-indigo-100 dark:border-indigo-800">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
            </span>
            A smarter way to learn
          </div>
          
          <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
            Remember <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400">Everything</span> You Learn.
          </h1>
          
          <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl mx-auto lg:mx-0">
            Tsuyanki uses spaced repetition to help you memorize languages, facts, and concepts efficiently. Study less, remember more.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
            <Button size="lg" as-child class="h-14 px-8 text-lg rounded-full bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all hover:scale-105">
              <RouterLink to="/register">
                Start Learning Free
                <ArrowRight class="ml-2 w-5 h-5" />
              </RouterLink>
            </Button>
            <Button size="lg" variant="outline" as-child class="h-14 px-8 text-lg rounded-full border-2 hover:bg-slate-50 dark:hover:bg-slate-900">
              <a href="#how-it-works">
                How It Works
              </a>
            </Button>
          </div>
        </div>

        <!-- Hero Visual/Abstract -->
        <div class="relative lg:h-[600px] flex items-center justify-center mt-8 lg:mt-0">
             <!-- Card Stack Animation -->
             <div class="relative w-72 h-96 sm:w-80 sm:h-[420px] scale-90 sm:scale-100 transform-gpu">
                <!-- Back Cards -->
                <div class="absolute inset-0 bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 transform translate-x-4 translate-y-4 rotate-6 opacity-60 scale-95 origin-bottom-right"></div>
                <div class="absolute inset-0 bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 transform -translate-x-4 translate-y-2 -rotate-3 opacity-80 scale-95 origin-bottom-left"></div>
                
                <!-- Main Card -->
                <div class="absolute inset-0 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center justify-center p-8 text-center transform hover:-translate-y-2 transition-transform duration-500">
                    <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/50 rounded-2xl flex items-center justify-center mb-6 text-indigo-600 dark:text-indigo-400">
                        <Globe class="w-8 h-8" />
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm uppercase tracking-wider text-slate-400 font-semibold">Japanese • JLPT N5</p>
                        <h3 class="text-4xl font-bold text-slate-800 dark:text-slate-100">猫</h3>
                        <p class="text-lg text-slate-500 dark:text-slate-400">(Neko)</p>
                    </div>
                    
                    <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800 w-full">
                         <div class="flex justify-between gap-2">
                             <div class="h-2 w-full bg-red-100 rounded-full overflow-hidden"><div class="h-full w-0 bg-red-500"></div></div>
                             <div class="h-2 w-full bg-orange-100 rounded-full overflow-hidden"><div class="h-full w-0 bg-orange-500"></div></div>
                             <div class="h-2 w-full bg-green-100 rounded-full overflow-hidden"><div class="h-full w-3/4 bg-green-500"></div></div>
                             <div class="h-2 w-full bg-blue-100 rounded-full overflow-hidden"><div class="h-full w-0 bg-blue-500"></div></div>
                         </div>
                         <p class="text-xs text-slate-400 mt-2">Recall Strength</p>
                    </div>
                </div>

                <!-- Floating Elements -->
                 <div class="absolute -right-8 sm:-right-12 top-10 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 animate-bounce duration-[3000ms]">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-full text-green-600"><CheckCircle2 class="w-4 h-4 sm:w-5 sm:h-5" /></div>
                        <div>
                            <p class="text-[10px] sm:text-xs text-slate-400">Status</p>
                            <p class="font-bold text-sm sm:text-base text-slate-800 dark:text-slate-200">Memorized</p>
                        </div>
                    </div>
                 </div>
                 
                 <div class="absolute -left-4 sm:-left-8 bottom-20 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 animate-bounce duration-[4000ms]">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-100 dark:bg-indigo-900/30 p-2 rounded-full text-indigo-600"><Clock class="w-4 h-4 sm:w-5 sm:h-5" /></div>
                        <div>
                            <p class="text-[10px] sm:text-xs text-slate-400">Next Review</p>
                            <p class="font-bold text-sm sm:text-base text-slate-800 dark:text-slate-200">3 Days</p>
                        </div>
                    </div>
                 </div>
             </div>
        </div>
      </div>
    </section>

    <!-- Problem & Insight -->
    <section class="py-16 sm:py-24 bg-white dark:bg-slate-900 relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-12 relative z-10">
            <div class="space-y-4">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100">Why do we forget?</h2>
                <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-400 leading-relaxed max-w-2xl mx-auto">
                    Information leaks out of our brains over time. It’s natural, but frustrating when you're trying to learn.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 text-left">
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-xl flex items-center justify-center text-orange-600 mb-4">
                        <Clock class="w-6 h-6" />
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-slate-900 dark:text-slate-100">The 24-Hour Drop</h3>
                    <p class="text-slate-600 dark:text-slate-400">You forget up to 70% of new information within one day without review.</p>
                </div>
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center text-red-600 mb-4">
                        <Zap class="w-6 h-6" />
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-slate-900 dark:text-slate-100">Wasted Effort</h3>
                    <p class="text-slate-600 dark:text-slate-400">Cramming works for a day, but spaced repetition works for a lifetime.</p>
                </div>
                <div class="p-6 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 ring-1 ring-indigo-200 dark:ring-indigo-800">
                     <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-600 mb-4">
                        <Brain class="w-6 h-6" />
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-indigo-900 dark:text-indigo-100">The Solution</h3>
                    <p class="text-slate-600 dark:text-slate-400">Reviewing at the <i>exact moment</i> you're about to forget strengthens the memory.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How Tsuyanki Works -->
    <section id="how-it-works" class="py-16 sm:py-24 px-4 sm:px-6 relative overflow-hidden">
        <div class="max-w-6xl mx-auto space-y-16 relative z-10">
            <div class="text-center space-y-4">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-slate-100">How Tsuyanki Works</h2>
                <p class="text-lg text-slate-600 dark:text-slate-400">A simple system for complex learning.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12 relative">
                 <!-- Connector line (desktop) -->
                 <div class="hidden md:block absolute top-24 left-[20%] right-[20%] h-0.5 bg-gradient-to-r from-indigo-200/0 via-indigo-300 dark:via-indigo-700 to-indigo-200/0 border-t border-dashed border-indigo-400 dark:border-indigo-600"></div>

                <div class="relative flex flex-col items-center text-center space-y-6">
                    <div class="relative z-10 w-20 h-20 bg-white dark:bg-slate-900 rounded-2xl shadow-lg border-2 border-indigo-100 dark:border-indigo-900 flex items-center justify-center text-indigo-600">
                        <BookOpen class="w-10 h-10" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">1. Create Decks</h3>
                        <p class="text-slate-600 dark:text-slate-400">Organize your study material into decks. Import from Anki or create your own.</p>
                    </div>
                </div>

                <div class="relative flex flex-col items-center text-center space-y-6">
                     <div class="relative z-10 w-20 h-20 bg-white dark:bg-slate-900 rounded-2xl shadow-lg border-2 border-indigo-100 dark:border-indigo-900 flex items-center justify-center text-indigo-600">
                        <Repeat class="w-10 h-10" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">2. Daily Review</h3>
                        <p class="text-slate-600 dark:text-slate-400">Tsuyanki schedules cards for you. Just show up and review what's due.</p>
                    </div>
                </div>

                <div class="relative flex flex-col items-center text-center space-y-6">
                     <div class="relative z-10 w-20 h-20 bg-white dark:bg-slate-900 rounded-2xl shadow-lg border-2 border-indigo-100 dark:border-indigo-900 flex items-center justify-center text-indigo-600">
                        <Smile class="w-10 h-10" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">3. Master It</h3>
                        <p class="text-slate-600 dark:text-slate-400">Watch your retention rate soar. Learn efficiently without burnout.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Visual Feature Preview -->
    <section class="py-16 sm:py-24 bg-slate-900 text-white overflow-hidden relative">
        <!-- Background Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] sm:w-[800px] h-[600px] sm:h-[800px] bg-indigo-600/20 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
                <h2 class="text-3xl md:text-4xl font-bold">Designed for focus.</h2>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <Layers class="w-6 h-6 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Anki Compatibility</h3>
                            <p class="text-slate-400 leading-relaxed">
                                Moving from Anki? We support importing your existing decks (apkg) so you don't lose any progress.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                         <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <Zap class="w-6 h-6 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Smart Scheduling</h3>
                            <p class="text-slate-400 leading-relaxed">
                                Our SM-2 based algorithm handles the math. You just focus on the content.
                            </p>
                        </div>
                    </div>
                     <div class="flex gap-4">
                         <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <BookOpen class="w-6 h-6 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Rich Text Editor</h3>
                            <p class="text-slate-400 leading-relaxed">
                                Create beautiful cards with images, audio, and rich formatting effortlessly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Abstract UI Preview -->
            <div class="relative rounded-xl bg-slate-800/50 border border-slate-700/50 p-2 shadow-2xl lg:skew-y-3 transform hover:skew-y-0 transition-transform duration-700">
                <div class="rounded-lg overflow-hidden bg-slate-950 aspect-video relative flex flex-col">
                    <!-- Fake Window Header -->
                    <div class="h-8 bg-slate-900 border-b border-slate-800 flex items-center px-4 gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500/50"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500/50"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500/50"></div>
                    </div>
                    <!-- Fake Content -->
                    <div class="flex-1 p-8 flex items-center justify-center">
                         <div class="w-full max-w-sm space-y-6">
                            <div class="h-4 w-32 bg-slate-800 rounded mx-auto"></div>
                            <div class="h-12 w-3/4 bg-slate-800 rounded mx-auto"></div>
                            <div class="h-32 w-full bg-slate-800/50 rounded border border-slate-800 border-dashed flex items-center justify-center">
                                <span class="text-slate-700 text-sm">Image / Audio</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2 pt-4">
                                <div class="h-10 bg-indigo-500/20 rounded"></div>
                                <div class="h-10 bg-indigo-500/20 rounded"></div>
                                <div class="h-10 bg-indigo-500/20 rounded"></div>
                                <div class="h-10 bg-indigo-500/20 rounded"></div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 sm:py-24 px-4 sm:px-6 relative">
        <div class="max-w-4xl mx-auto text-center space-y-8 relative z-10">
            <h2 class="text-3xl md:text-5xl font-bold text-slate-900 dark:text-white tracking-tight">
                Ready to stop forgetting?
            </h2>
            <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                Join others who are mastering languages and exams with Tsuyanki.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                 <Button size="lg" as-child class="h-14 px-10 text-lg rounded-full bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all hover:scale-105">
                    <RouterLink to="/register">Create Account</RouterLink>
                </Button>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-6">
                No credit card required.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 py-12 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-1 space-y-4">
                 <div class="flex items-center gap-2">
                    <img src="/logo.png" alt="Tsuyanki" class="w-6 h-6 object-contain" />
                    <span class="font-bold text-lg text-slate-900 dark:text-white">Tsuyanki</span>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    A spaced-repetition learning tool designed for simplicity and focus.
                </p>
            </div>
            
            <div class="col-span-1 md:col-span-3 flex flex-col md:flex-row justify-end gap-8 md:gap-16 text-sm text-slate-600 dark:text-slate-400">
                <div class="flex flex-col gap-3">
                    <h4 class="font-bold text-slate-900 dark:text-slate-200">Product</h4>
                    <RouterLink to="/register" class="hover:text-indigo-600 dark:hover:text-indigo-400">Sign Up</RouterLink>
                    <RouterLink to="/login" class="hover:text-indigo-600 dark:hover:text-indigo-400">Log In</RouterLink>

                </div>
                <!-- <div class="flex flex-col gap-3">
                    <h4 class="font-bold text-slate-900 dark:text-slate-200">Legal</h4>
                    <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400">Privacy Policy</a>
                    <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400">Terms of Service</a>
                </div> -->
            </div>
        </div>
        <div class="max-w-7xl mx-auto mt-12 pt-8 border-t border-slate-100 dark:border-slate-900 text-center text-xs text-slate-400">
            &copy; {{ new Date().getFullYear() }} MaulIshak. All rights reserved.
        </div>
    </footer>
  </div>
</template>

<style scoped>
/* Any specific component-only styles if needed beyond Tailwind */
</style>
