<script setup>
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert'
import { Separator } from '@/components/ui/separator'
import {
  Clock,
  Brain,
  Repeat,
  Layers,
  FileText,
  HelpCircle,
  CalendarCheck,
  Zap,
  Layout,
  BookOpen,
  Users as UsersIcon,
  ArrowDown,
} from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans transition-colors duration-300">
    <!-- Page Header -->
    <header class="bg-indigo-600 dark:bg-indigo-900/50 text-white py-20 px-4 sm:px-6 lg:px-8 text-center relative overflow-hidden mb-12">
      <!-- Abstract Background Shapes -->
      <div class="absolute inset-0 opacity-20 pointer-events-none">
         <div class="absolute top-0 left-0 w-64 h-64 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
         <div class="absolute top-0 right-0 w-64 h-64 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
         <div class="absolute -bottom-32 left-20 w-64 h-64 bg-pink-400 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>
      </div>

      <div class="relative max-w-3xl mx-auto space-y-6 z-10">
        <h1 class="text-4xl sm:text-6xl font-bold tracking-tight text-white drop-shadow-sm">About Tsuyanki</h1>
        <p class="text-lg sm:text-2xl text-indigo-100 font-medium max-w-2xl mx-auto leading-relaxed">
          A simple, focused way to learn deeply using the power of spaced repetition.
        </p>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 space-y-20">
      
      <!-- Why Tsuyanki Exists -->
      <section class="max-w-4xl mx-auto text-center space-y-8">
        <div class="space-y-4">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Why Tsuyanki?</h2>
            <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl mx-auto">
            We've all been there: studying hard for an exam or learning a new language, only to forget it a week later.
            The brain naturally discards information it deems unnecessary over time.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
            <Card class="bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-100 dark:border-indigo-900/50 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <CardContent class="p-6 relative z-10">
                    <div class="mb-4 bg-white dark:bg-slate-900 w-12 h-12 rounded-lg flex items-center justify-center text-indigo-600 shadow-sm">
                        <Brain class="w-6 h-6" />
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-indigo-900 dark:text-indigo-200">The Forgetting Curve</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Without review, memories fade quickly. Tsuyanki interrupts this curve.</p>
                </CardContent>
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-200 dark:bg-indigo-800 rounded-full opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
            </Card>

            <Card class="bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/50 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <CardContent class="p-6 relative z-10">
                     <div class="mb-4 bg-white dark:bg-slate-900 w-12 h-12 rounded-lg flex items-center justify-center text-emerald-600 shadow-sm">
                        <CalendarCheck class="w-6 h-6" />
                    </div>
                    <h3 class="font-bold text-lg mb-2 text-emerald-900 dark:text-emerald-200">Efficient Learning</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Review only what you are about to forget, saving you hours of study time.</p>
                </CardContent>
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-200 dark:bg-emerald-800 rounded-full opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
            </Card>
        </div>
      </section>

      <!-- What Is Spaced Repetition? -->
      <section class="space-y-12">
        <div class="text-center space-y-4">
          <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">The Power of Spaced Repetition</h2>
          <p class="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto text-lg">
            Review at increasing intervals to move knowledge from short-term to long-term memory.
          </p>
        </div>

        <div class="relative">
             <!-- Connector Line (Desktop) -->
            <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-transparent via-indigo-200 dark:via-indigo-800 to-transparent z-0"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            <div class="flex flex-col items-center text-center space-y-4 p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                <div class="bg-indigo-50 dark:bg-indigo-900/50 p-4 rounded-full text-indigo-600 dark:text-indigo-400 ring-4 ring-white dark:ring-slate-950">
                <BookOpen class="w-8 h-8" />
                </div>
                <div>
                    <h3 class="font-bold text-xl mb-2">1. Learn</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">You encounter a new concept or word for the first time.</p>
                </div>
            </div>
            <div class="flex flex-col items-center text-center space-y-4 p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                <div class="bg-indigo-50 dark:bg-indigo-900/50 p-4 rounded-full text-indigo-600 dark:text-indigo-400 ring-4 ring-white dark:ring-slate-950">
                <Clock class="w-8 h-8" />
                </div>
                <div>
                    <h3 class="font-bold text-xl mb-2">2. Wait</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Time passes. The memory begins to fade naturally.</p>
                </div>
            </div>
            <div class="flex flex-col items-center text-center space-y-4 p-6 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                <div class="bg-indigo-50 dark:bg-indigo-900/50 p-4 rounded-full text-indigo-600 dark:text-indigo-400 ring-4 ring-white dark:ring-slate-950">
                <Repeat class="w-8 h-8" />
                </div>
                <div>
                    <h3 class="font-bold text-xl mb-2">3. Recall</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Review just before you forget to strengthen the neural pathway.</p>
                </div>
            </div>
            </div>
        </div>
      </section>

      <!-- How Tsuyanki Works (Core Concepts) -->
      <section class="space-y-12">
        <div class="text-center">
          <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100 mb-4">How Tsuyanki Works</h2>
          <p class="text-slate-600 dark:text-slate-400">Understanding the core building blocks.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            
          <!-- Concepts List -->
          <div class="space-y-4">
            <Card class="border-l-4 border-l-indigo-500 shadow-sm hover:shadow-md transition-shadow">
              <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-lg">
                  <Layers class="w-5 h-5 text-indigo-600" />
                  Deck
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-slate-600 dark:text-slate-400 text-sm">A collection of related topics (e.g., "Japanese N5 Vocabulary").</p>
              </CardContent>
            </Card>

            <Card class="border-l-4 border-l-blue-500 shadow-sm hover:shadow-md transition-shadow">
              <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-lg">
                  <FileText class="w-5 h-5 text-blue-600" />
                  Note
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-slate-600 dark:text-slate-400 text-sm">One piece of knowledge (e.g., The word "Neko" / "Cat").</p>
              </CardContent>
            </Card>

            <Card class="border-l-4 border-l-emerald-500 shadow-sm hover:shadow-md transition-shadow">
              <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-lg">
                  <HelpCircle class="w-5 h-5 text-emerald-600" />
                  Card
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-slate-600 dark:text-slate-400 text-sm">A question generated from a note (e.g., Front: "Neko", Back: "Cat").</p>
              </CardContent>
            </Card>
          </div>

          <!-- Diagram -->
          <div class="bg-slate-50 dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center space-y-6">
             <div class="font-bold text-slate-900 dark:text-slate-100 mb-2 uppercase tracking-wide text-sm opacity-50">data flow</div>
             
             <div class="flex flex-col items-center w-full max-w-xs gap-4 relative">
                <!-- Line -->
                <div class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-700 -z-10 -translate-x-1/2"></div>

                <div class="w-full bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4 relative z-10">
                    <div class="bg-blue-100 text-blue-600 p-2 rounded-lg"><FileText class="w-5 h-5"/></div>
                    <div>
                        <div class="font-bold text-sm">Input Note</div>
                        <div class="text-xs text-slate-500">"Neko" : "Cat"</div>
                    </div>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 border-4 border-white dark:border-slate-900 flex items-center justify-center z-10 text-slate-400">
                    <ArrowDown class="w-4 h-4" />
                </div>

                 <div class="w-full bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4 relative z-10">
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg"><HelpCircle class="w-5 h-5"/></div>
                     <div>
                        <div class="font-bold text-sm">Generated Card</div>
                        <div class="text-xs text-slate-500">Q: Neko -> A: Cat</div>
                    </div>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 border-4 border-white dark:border-slate-900 flex items-center justify-center z-10 text-slate-400">
                    <ArrowDown class="w-4 h-4" />
                </div>

                 <div class="w-full bg-indigo-600 p-4 rounded-xl shadow-lg border border-indigo-500 flex items-center gap-4 relative z-10 text-white">
                    <div class="bg-white/20 p-2 rounded-lg"><Repeat class="w-5 h-5"/></div>
                    <div class="font-bold">Daily Review</div>
                </div>
             </div>
          </div>

        </div>
      </section>

      <!-- What Happens During a Review Session -->
      <section class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100 text-center">Your Daily Review Session</h2>
        
        <p class="text-slate-600 dark:text-slate-400 text-center">
            When you open Tsuyanki, your goals are simple. Here is what happens:
        </p>

        <Accordion type="single" collapsible class="w-full bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 px-4">
            <AccordionItem value="item-1">
              <AccordionTrigger class="dark:text-slate-200">1. Tsuyanki selects cards due for review</AccordionTrigger>
              <AccordionContent class="text-slate-600 dark:text-slate-400">
                You don't need to decide what to study. The app calculates which cards you are at risk of forgetting and shows them to you.
              </AccordionContent>
            </AccordionItem>
            <AccordionItem value="item-2">
              <AccordionTrigger class="dark:text-slate-200">2. You answer and rate difficulty</AccordionTrigger>
              <AccordionContent class="text-slate-600 dark:text-slate-400 space-y-2">
                <p>After seeing the answer, you tell Tsuyanki how hard it was named:</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs font-semibold mt-2">
                    <span class="bg-red-100 text-red-700 py-1 rounded">Again</span>
                    <span class="bg-orange-100 text-orange-700 py-1 rounded">Hard</span>
                    <span class="bg-green-100 text-green-700 py-1 rounded">Good</span>
                    <span class="bg-blue-100 text-blue-700 py-1 rounded">Easy</span>
                </div>
              </AccordionContent>
            </AccordionItem>
            <AccordionItem value="item-3">
              <AccordionTrigger class="dark:text-slate-200">3. Connect for long-term memory</AccordionTrigger>
              <AccordionContent class="text-slate-600 dark:text-slate-400">
                Based on your rating, Tsuyanki schedules the next review. Easier cards are shown less often, while harder ones appear sooner.
              </AccordionContent>
            </AccordionItem>
        </Accordion>
      </section>

      <!-- Key Benefits & Who It Is For -->
      <section class="grid grid-cols-1 md:grid-cols-2 gap-12">
          
          <div class="space-y-6">
              <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                  <Zap class="w-6 h-6 text-indigo-600 dark:text-indigo-400" /> Key Benefits
              </h2>
              <ul class="space-y-3">
                  <li class="flex items-start gap-3">
                      <CalendarCheck class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700 dark:text-slate-300"><strong>No manual scheduling:</strong> Just open the app and review what's due.</span>
                  </li>
                  <li class="flex items-start gap-3">
                      <Layout class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700 dark:text-slate-300"><strong>Daily manageable workload:</strong> Short, focused sessions are better than infrequent binges.</span>
                  </li>
                  <li class="flex items-start gap-3">
                      <Brain class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700 dark:text-slate-300"><strong>Long-term focus:</strong> Optimized for retaining knowledge for years, not days.</span>
                  </li>
                  <li class="flex items-start gap-3">
                      <Layers class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700 dark:text-slate-300"><strong>Anki Compatible:</strong> Import your existing decks easily.</span>
                  </li>
              </ul>
          </div>

          <div class="space-y-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <UsersIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" /> Who is Tsuyanki For?
            </h2>
            <div class="space-y-4">
                <Alert class="bg-indigo-50 dark:bg-indigo-950/30 border-indigo-100 dark:border-indigo-900/50">
                    <AlertTitle class="text-indigo-800 dark:text-indigo-200 font-semibold">Language Learners</AlertTitle>
                    <AlertDescription class="text-indigo-700/80 dark:text-indigo-300/80">
                        Perfect for vocabulary, kanji, or phrase memorization.
                    </AlertDescription>
                </Alert>
                <Alert class="bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-800">
                    <AlertTitle class="text-slate-800 dark:text-slate-200 font-semibold">Students</AlertTitle>
                    <AlertDescription class="text-slate-600 dark:text-slate-400">
                        Ace your exams by retaining facts, formulas, and definitions.
                    </AlertDescription>
                </Alert>
                <Alert class="bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-800">
                    <AlertTitle class="text-slate-800 dark:text-slate-200 font-semibold">Lifelong Learners</AlertTitle>
                    <AlertDescription class="text-slate-600 dark:text-slate-400">
                        Anyone who wants to keep their knowledge sharp without the stress.
                    </AlertDescription>
                </Alert>
            </div>
          </div>

      </section>

      <!-- Footer -->
      <footer class="text-center text-slate-500 dark:text-slate-400 text-sm py-8 border-t border-slate-200 dark:border-slate-800 mt-12">
        <p>Created by MaulIshak</p>
      </footer>

    </main>
  </div>
</template>

<script>
// Additional Icon import for use in the template that wasn't in setup
import { Users as UsersIcon } from 'lucide-vue-next';
</script>
