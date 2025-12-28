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
} from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 font-sans">
    <!-- Page Header -->
    <header class="bg-indigo-600 text-white py-16 px-4 sm:px-6 lg:px-8 text-center relative overflow-hidden">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
          <circle cx="80" cy="80" r="15" fill="currentColor" />
          <circle cx="20" cy="20" r="25" fill="currentColor" />
        </svg>
      </div>
      <div class="relative max-w-3xl mx-auto space-y-4">
        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight">About Tsuyanki</h1>
        <p class="text-xl sm:text-2xl text-indigo-100 font-medium">
          A simple, focused way to learn using spaced repetition
        </p>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
      
      <!-- Why Tsuyanki Exists -->
      <section class="max-w-3xl mx-auto text-center space-y-6">
        <h2 class="text-3xl font-bold text-slate-900">Why Tsuyanki?</h2>
        <p class="text-lg text-slate-600 leading-relaxed">
          We've all been there: you study hard for an exam or learn a new word, only to forget it a week later.
          Learning once is not enough. The brain naturally discards information it deems unnecessary over time.
        </p>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 inline-block text-left">
          <p class="text-lg text-indigo-700 font-medium italic">
            "Tsuyanki helps you review material at the right time, before you forget it."
          </p>
        </div>
      </section>

      <Separator />

      <!-- What Is Spaced Repetition? -->
      <section class="space-y-8">
        <div class="text-center space-y-2">
          <h2 class="text-3xl font-bold text-slate-900">What Is Spaced Repetition?</h2>
          <p class="text-slate-600 max-w-2xl mx-auto">
            It's a study technique that schedules reviews at increasing intervals. Instead of cramming, you review just as you're about to forget.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
          <div class="flex flex-col items-center text-center space-y-4 p-4">
            <div class="bg-indigo-100 p-4 rounded-full text-indigo-600">
              <BookOpen class="w-8 h-8" />
            </div>
            <h3 class="font-semibold text-lg">1. Learn</h3>
            <p class="text-slate-600 text-sm">You encounter a new concept or word for the first time.</p>
          </div>
          <div class="flex flex-col items-center text-center space-y-4 p-4">
            <div class="bg-indigo-100 p-4 rounded-full text-indigo-600">
              <Clock class="w-8 h-8" />
            </div>
            <h3 class="font-semibold text-lg">2. Wait</h3>
            <p class="text-slate-600 text-sm">Time passes. The memory begins to fade naturally.</p>
          </div>
          <div class="flex flex-col items-center text-center space-y-4 p-4">
            <div class="bg-indigo-100 p-4 rounded-full text-indigo-600">
              <Repeat class="w-8 h-8" />
            </div>
            <h3 class="font-semibold text-lg">3. Recall</h3>
            <p class="text-slate-600 text-sm">Tsuyanki reminds you to review just before you forget, strengthening the memory.</p>
          </div>
        </div>
      </section>

      <Separator />

      <!-- How Tsuyanki Works (Core Concepts) -->
      <section class="space-y-10">
        <div class="text-center space-y-2">
          <h2 class="text-3xl font-bold text-slate-900">How Tsuyanki Works</h2>
          <p class="text-slate-600">Understanding the core building blocks.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            
          <!-- Concepts List -->
          <div class="space-y-6">
            <Card>
              <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-lg">
                  <Layers class="w-5 h-5 text-indigo-600" />
                  Deck
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-slate-600 text-sm">A collection of related topics (e.g., "Japanese N5 Vocabulary").</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-lg">
                  <FileText class="w-5 h-5 text-indigo-600" />
                  Note
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-slate-600 text-sm">One piece of knowledge (e.g., The word "Neko" / "Cat").</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-lg">
                  <HelpCircle class="w-5 h-5 text-indigo-600" />
                  Card
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-slate-600 text-sm">A question generated from a note (e.g., Front: "Neko", Back: "Cat").</p>
              </CardContent>
            </Card>
          </div>

          <!-- Diagram -->
          <div class="bg-indigo-50 p-8 rounded-xl border border-indigo-100 flex flex-col items-center justify-center space-y-4">
             <div class="font-semibold text-indigo-900 mb-2">The Flow</div>
             
             <div class="flex flex-col items-center gap-2">
                <div class="bg-white px-6 py-3 rounded-lg shadow-sm border border-indigo-200 text-indigo-800 font-medium">Note</div>
                <div class="h-6 w-0.5 bg-indigo-300"></div>
                <div class="bg-white px-6 py-3 rounded-lg shadow-sm border border-indigo-200 text-indigo-800 font-medium">Card</div>
                <div class="h-6 w-0.5 bg-indigo-300"></div>
                <div class="bg-indigo-600 px-6 py-3 rounded-lg shadow-md text-white font-bold flex items-center gap-2">
                  <Repeat class="w-4 h-4" /> Review
                </div>
             </div>
             
             <p class="text-xs text-center text-indigo-600 mt-4 max-w-xs">
                You create a note, it becomes a card, and Tsuyanki schedules it for daily review.
             </p>
          </div>

        </div>
      </section>

      <!-- What Happens During a Review Session -->
      <section class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-3xl font-bold text-slate-900 text-center">Your Daily Review Session</h2>
        
        <p class="text-slate-600 text-center">
            When you open Tsuyanki, your goals are simple. Here is what happens:
        </p>

        <Accordion type="single" collapsible class="w-full bg-white rounded-lg border border-slate-200 px-4">
            <AccordionItem value="item-1">
              <AccordionTrigger>1. Tsuyanki selects cards due for review</AccordionTrigger>
              <AccordionContent class="text-slate-600">
                You don't need to decide what to study. The app calculates which cards you are at risk of forgetting and shows them to you.
              </AccordionContent>
            </AccordionItem>
            <AccordionItem value="item-2">
              <AccordionTrigger>2. You answer and rate difficulty</AccordionTrigger>
              <AccordionContent class="text-slate-600 space-y-2">
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
              <AccordionTrigger>3. Connect for long-term memory</AccordionTrigger>
              <AccordionContent class="text-slate-600">
                Based on your rating, Tsuyanki schedules the next review. Easier cards are shown less often, while harder ones appear sooner.
              </AccordionContent>
            </AccordionItem>
        </Accordion>
      </section>

      <!-- Key Benefits & Who It Is For -->
      <section class="grid grid-cols-1 md:grid-cols-2 gap-12">
          
          <div class="space-y-6">
              <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                  <Zap class="w-6 h-6 text-indigo-600" /> Key Benefits
              </h2>
              <ul class="space-y-3">
                  <li class="flex items-start gap-3">
                      <CalendarCheck class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700"><strong>No manual scheduling:</strong> Just open the app and review what's due.</span>
                  </li>
                  <li class="flex items-start gap-3">
                      <Layout class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700"><strong>Daily manageable workload:</strong> Short, focused sessions are better than infrequent binges.</span>
                  </li>
                  <li class="flex items-start gap-3">
                      <Brain class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700"><strong>Long-term focus:</strong> Optimized for retaining knowledge for years, not days.</span>
                  </li>
                  <li class="flex items-start gap-3">
                      <Layers class="w-5 h-5 text-indigo-500 mt-0.5 shrink-0" />
                      <span class="text-slate-700"><strong>Anki Compatible:</strong> Import your existing decks easily.</span>
                  </li>
              </ul>
          </div>

          <div class="space-y-6">
            <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <UsersIcon class="w-6 h-6 text-indigo-600" /> Who is Tsuyanki For?
            </h2>
            <div class="space-y-4">
                <Alert class="bg-indigo-50 border-indigo-100">
                    <AlertTitle class="text-indigo-800 font-semibold">Language Learners</AlertTitle>
                    <AlertDescription class="text-indigo-700/80">
                        Perfect for vocabulary, kanji, or phrase memorization.
                    </AlertDescription>
                </Alert>
                <Alert class="bg-slate-50 border-slate-200">
                    <AlertTitle class="text-slate-800 font-semibold">Students</AlertTitle>
                    <AlertDescription class="text-slate-600">
                        Ace your exams by retaining facts, formulas, and definitions.
                    </AlertDescription>
                </Alert>
                <Alert class="bg-slate-50 border-slate-200">
                    <AlertTitle class="text-slate-800 font-semibold">Lifelong Learners</AlertTitle>
                    <AlertDescription class="text-slate-600">
                        Anyone who wants to keep their knowledge sharp without the stress.
                    </AlertDescription>
                </Alert>
            </div>
          </div>

      </section>

      <!-- Footer -->
      <footer class="text-center text-slate-500 text-sm py-8 border-t border-slate-200 mt-12">
        <p>Created by MaulIshak</p>
      </footer>

    </main>
  </div>
</template>

<script>
// Additional Icon import for use in the template that wasn't in setup
import { Users as UsersIcon } from 'lucide-vue-next';
</script>
