
import { mergeAttributes, Node } from '@tiptap/core';

export const VideoNode = Node.create({
    name: 'video',

    group: 'block',

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
                tag: 'video',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['video', mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return ({ node }) => {
            const dom = document.createElement('video');
            dom.src = node.attrs.src;
            dom.controls = true;
            dom.style.maxWidth = '100%';
            return {
                dom,
            };
        };
    },

    addCommands() {
        return {
            setVideo:
                (src) =>
                    ({ commands }) => {
                        return commands.insertContent({
                            type: 'video',
                            attrs: { src },
                        });
                    },
        };
    },
});
