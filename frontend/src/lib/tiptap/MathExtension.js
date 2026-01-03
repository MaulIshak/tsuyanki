import { mergeAttributes, Node } from '@tiptap/core';
import katex from 'katex';
import 'katex/dist/katex.min.css';

export const MathNode = Node.create({
    name: 'math',

    group: 'inline',

    inline: true,

    atom: true,

    addAttributes() {
        return {
            expression: {
                default: 'E=mc^2',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-type="math"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-type': 'math' })];
    },

    addNodeView() {
        return ({ node, HTMLAttributes, js }) => {
            const dom = document.createElement('span');
            dom.classList.add('math-node', 'cursor-pointer', 'px-1', 'bg-slate-100', 'dark:bg-slate-800', 'rounded');
            dom.setAttribute('data-type', 'math');

            const render = () => {
                try {
                    katex.render(node.attrs.expression, dom, {
                        throwOnError: false,
                        output: 'mathml', // or html
                    });
                } catch (e) {
                    dom.textContent = node.attrs.expression;
                }
            };

            render();

            // Simple click to edit (using prompt for now for speed/simplicity, custom UI later)
            dom.addEventListener('click', () => {
                // In a real app, we'd open a modal or popover.
                // For MVP, we can trust the toolbar or complex interaction. 
                // But to edit inline, let's just assume we delete and re-insert or use toolbar.
                // Let's rely on the toolbar to effectively "replace" selection or insert.
                // Or adding a temporary click handler:
                // const newExp = prompt("Edit Equation:", node.attrs.expression);
                // if (newExp !== null) {
                //   // We can't easily dispatch update from here without `editor` instance access elegantly or view dispatch.
                //   // It's better to treat as atom and delete/insert.
                // }
            });

            return {
                dom,
            };
        };
    },

    addCommands() {
        return {
            setMath:
                (expression) =>
                    ({ commands }) => {
                        return commands.insertContent({
                            type: 'math',
                            attrs: { expression },
                        });
                    },
        };
    },
});
