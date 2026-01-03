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
import { watch, onBeforeUnmount } from 'vue'

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

// Toolbar Actions
const toggleBold = () => editor.value.chain().focus().toggleBold().run()
const toggleItalic = () => editor.value.chain().focus().toggleItalic().run()
const toggleStrike = () => editor.value.chain().focus().toggleStrike().run()
const toggleUnderline = () => editor.value.chain().focus().toggleUnderline().run()
const toggleSubscript = () => editor.value.chain().focus().toggleSubscript().run()
const toggleSuperscript = () => editor.value.chain().focus().toggleSuperscript().run()

const addMath = () => {
    const expression = prompt('Enter LaTeX equation (e.g. E=mc^2):', 'E=mc^2')
    if (expression) {
        editor.value.chain().focus().setMath(expression).run()
    }
}

const addFurigana = () => {
    const text = prompt('Enter text (Kanji):')
    if (!text) return
    const reading = prompt('Enter reading (Kana):')
    if (reading) {
        editor.value.chain().focus().setFurigana(text, reading).run()
    }
}

const triggerMediaUpload = () => {
    emit('on-media-upload')
}

const addLink = () => {
    const previousUrl = editor.value.getAttributes('link').href
    const url = window.prompt('URL', previousUrl)

    if (url === null) {
        return
    }

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
        return
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
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
            
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="addMath" title="Insert Equation">
                <Sigma class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="addFurigana" title="Insert Furigana">
                <Type class="w-4 h-4" /> <!-- Using Type icon for Furigana/Text -->
            </Button>
            
             <div class="w-px h-6 bg-slate-300 dark:bg-slate-700 mx-1"></div>

            <Button size="icon" variant="ghost" class="h-8 w-8" @click="addLink" :class="{ 'bg-slate-200 dark:bg-slate-800': editor.isActive('link') }">
                <LinkIcon class="w-4 h-4" />
            </Button>
            <Button size="icon" variant="ghost" class="h-8 w-8" @click="triggerMediaUpload" title="Insert Media">
                 <Paperclip class="w-4 h-4" />
            </Button>
        </div>

        <EditorContent :editor="editor" class="p-4 min-h-[150px] prose dark:prose-invert max-w-none focus:outline-none" />
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
