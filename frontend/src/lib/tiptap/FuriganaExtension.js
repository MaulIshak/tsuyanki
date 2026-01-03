
import { mergeAttributes, Node } from '@tiptap/core';

export const Furigana = Node.create({
    name: 'furigana',

    group: 'inline',

    inline: true,

    atom: true,

    addAttributes() {
        return {
            text: {
                default: '',
            },
            reading: {
                default: '',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'ruby',
                getAttrs: (element) => {
                    // This is a simplified parser. Robust one would need to traverse children.
                    // Assuming <ruby>text<rt>reading</rt></ruby>
                    const text = element.childNodes[0]?.textContent || '';
                    const reading = element.querySelector('rt')?.textContent || '';
                    return { text, reading };
                }
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'ruby',
            mergeAttributes(HTMLAttributes),
            HTMLAttributes.text,
            ['rt', HTMLAttributes.reading],
        ];
    },

    addCommands() {
        return {
            setFurigana:
                (text, reading) =>
                    ({ commands }) => {
                        return commands.insertContent({
                            type: 'furigana',
                            attrs: { text, reading },
                        });
                    },
        };
    },
});
