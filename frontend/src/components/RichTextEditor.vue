<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'
import Placeholder from '@tiptap/extension-placeholder'
import { Furigana } from '@/lib/tiptap/FuriganaExtension'
import { MathNode } from '@/lib/tiptap/MathExtension'
import { AudioNode } from '@/lib/tiptap/AudioExtension'
import { VideoNode } from '@/lib/tiptap/VideoExtension'
import { 
    Bold, Italic, Strikethrough, Underline as UnderlineIcon, 
    Link as LinkIcon, Image as ImageIcon, Music, Video, 
    Superscript as SuperscriptIcon, Subscript as SubscriptIcon,
    Sigma, Type, Paperclip
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { watch, onBeforeUnmount, ref, reactive } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Type content...'
    }
})

const emit = defineEmits(['update:modelValue', 'on-media-upload'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Subscript,
        Superscript,
        Underline,
        Link.configure({ openOnClick: false }),
        Image.configure({ inline: true }),
        Placeholder.configure({ placeholder: props.placeholder }),
        Furigana,
        MathNode,
        AudioNode,
        VideoNode,
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
})

// Watch modelValue to update editor content if it changes externally
watch(() => props.modelValue, (value) => {
    // Only update if content is different to avoid cursor jumps or loops
    const isSame = editor.value?.getHTML() === value
    if (editor.value && !isSame) {
        editor.value.commands.setContent(value, false)
    }
})

// Modal States
const linkModal = reactive({ open: false, url: '' })
const mathModal = reactive({ open: false, expression: 'E=mc^2' })
const furiganaModal = reactive({ open: false, text: '', reading: '' })

// Toolbar Actions
const toggleBold = () => editor.value.chain().focus().toggleBold().run()
const toggleItalic = () => editor.value.chain().focus().toggleItalic().run()
const toggleStrike = () => editor.value.chain().focus().toggleStrike().run()
const toggleUnderline = () => editor.value.chain().focus().toggleUnderline().run()
const toggleSubscript = () => editor.value.chain().focus().toggleSubscript().run()
const toggleSuperscript = () => editor.value.chain().focus().toggleSuperscript().run()

const openMathModal = () => {
    mathModal.expression = 'E=mc^2' // Reset or keep previous?
    mathModal.open = true
}

const saveMath = () => {
    if (mathModal.expression) {
        editor.value.chain().focus().setMath(mathModal.expression).run()
    }
    mathModal.open = false
}

const openFuriganaModal = () => {
    // Try to get current selection if it's text
    const { from, to, empty } = editor.value.state.selection
    if (!empty) {
        furiganaModal.text = editor.value.state.doc.textBetween(from, to)
    } else {
        furiganaModal.text = ''
    }
    furiganaModal.reading = ''
    furiganaModal.open = true
}

const saveFurigana = () => {
    if (furiganaModal.text) {
        editor.value.chain().focus().setFurigana(furiganaModal.text, furiganaModal.reading).run()
    }
    furiganaModal.open = false
}

const triggerMediaUpload = () => {
    emit('on-media-upload')
}

const openLinkModal = () => {
    const previousUrl = editor.value.getAttributes('link').href
    linkModal.url = previousUrl || ''
    linkModal.open = true
}

const saveLink = () => {
    if (linkModal.url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
    } else {
         editor.value.chain().focus().extendMarkRange('link').setLink({ href: linkModal.url }).run()
    }
    linkModal.open = false
}

onBeforeUnmount(() => {
    editor.value?.destroy()
})

const insertMedia = (type, src) => {
    if (type === 'audio') {
        editor.value.chain().focus().setAudio(src).run()
    } else if (type === 'video') {
         editor.value.chain().focus().setVideo(src).run()
    } else if (type === 'image') {
        editor.value.chain().focus().setImage({ src }).run()
    }
}

// Expose internal commands if parent needs them
defineExpose({
    editor,
    insertMedia
})
</script>

<template>
    <div class="border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden bg-white dark:bg-slate-950 flex flex-col">
        <!-- Toolbar -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="toggleBold" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('bold') }">
                <Bold class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="toggleItalic" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('italic') }">
                <Italic class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="toggleUnderline" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('underline') }">
                <UnderlineIcon class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="toggleStrike" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('strike') }">
                <Strikethrough class="w-4 h-4" />
            </Button>
            
            <div class="w-px h-6 bg-slate-300 dark:bg-slate-700 mx-1"></div>

             <Button size="icon" variant="ghost" class="h-8 w-8" @click="toggleSubscript" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('subscript') }">
                <SubscriptIcon class="w-4 h-4" />
            </Button>
             <Button size="icon" variant="ghost" class="h-8 w-8" @click="toggleSuperscript" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('superscript') }">
                <SuperscriptIcon class="w-4 h-4" />
            </Button>
            
            <div class="w-px h-6 bg-slate-300 dark:bg-slate-700 mx-1"></div>
            
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="openMathModal" title="Insert Equation">
                <Sigma class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="openFuriganaModal" title="Insert Furigana">
                <Type class="w-4 h-4" /> <!-- Using Type icon for Furigana/Text -->
            </Button>
            
             <div class="w-px h-6 bg-slate-300 dark:bg-slate-700 mx-1"></div>

            <Button size="icon" variant="ghost" class="h-8 w-8" @click="openLinkModal" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('link') }">
                <LinkIcon class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="triggerMediaUpload" title="Insert Media">
                 <Paperclip class="w-4 h-4" />
            </Button>
        </div>

        <EditorContent :editor="editor" class="p-4 min-h-[150px] prose dark:prose-invert max-w-none focus:outline-none" />

        <!-- Link Modal -->
        <Dialog v-model:open="linkModal.open">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Insert Link</DialogTitle>
                    <DialogDescription>
                        Enter the URL to link to.
                    </DialogDescription>
                </DialogHeader>
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                         <Label htmlFor="linkUrl" class="sr-only">Link</Label>
                         <Input id="linkUrl" v-model="linkModal.url" placeholder="https://example.com" @keyup.enter="saveLink" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="linkModal.open = false">Cancel</Button>
                    <Button type="button" @click="saveLink">Save</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

         <!-- Math Modal -->
        <Dialog v-model:open="mathModal.open">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Insert Equation</DialogTitle>
                    <DialogDescription>
                        Enter a LaTeX equation.
                    </DialogDescription>
                </DialogHeader>
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                         <Label htmlFor="mathExpr" class="sr-only">Equation</Label>
                         <Input id="mathExpr" v-model="mathModal.expression" placeholder="E=mc^2" @keyup.enter="saveMath" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="mathModal.open = false">Cancel</Button>
                    <Button type="button" @click="saveMath">Insert</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Furigana Modal -->
        <Dialog v-model:open="furiganaModal.open">
             <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Insert Furigana</DialogTitle>
                    <DialogDescription>
                        Add reading aid (Furigana) to text.
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="furiText" class="text-right">Text</Label>
                        <Input id="furiText" v-model="furiganaModal.text" class="col-span-3" placeholder="Kanji" />
                    </div>
                     <div class="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="furiReading" class="text-right">Reading</Label>
                        <Input id="furiReading" v-model="furiganaModal.reading" class="col-span-3" placeholder="Kana" @keyup.enter="saveFurigana" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="furiganaModal.open = false">Cancel</Button>
                    <Button type="button" @click="saveFurigana">Insert</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style>
/* Prose Mirror Basic Styles */
.ProseMirror {
    outline: none;
    min-height: 100px;
}
.ProseMirror p.is-editor-empty:first-child::before {
  color: #adb5bd;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
