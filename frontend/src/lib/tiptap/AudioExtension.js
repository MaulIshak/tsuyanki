import { mergeAttributes, Node } from '@tiptap/core';

export const AudioNode = Node.create({
    name: 'audio',

    group: 'block', // Audio usually block or inline-block. Let's make it block for now to avoid layout issues.

    atom: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            controls: {
                default: true,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'audio',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['audio', mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return ({ node }) => {
            const dom = document.createElement('audio');
            dom.src = node.attrs.src;
            dom.controls = true;
            return {
                dom,
            };
        };
    },

    addCommands() {
        return {
            setAudio:
                (src) =>
                    ({ commands }) => {
                        return commands.insertContent({
                            type: 'audio',
                            attrs: { src },
                        });
                    },
        };
    },
});
