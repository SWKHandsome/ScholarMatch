const prefix = 'scholarmatch-rich-text-v1:';
function editorDocument(node) {
    return {
        ...node,
        type: node.type === 'bulletList' && node.attrs?.listStyle === 'dash' ? 'dashList' : node.type,
        ...(node.content ? { content: node.content.map(editorDocument) } : {}),
    };
}

export function scholarshipTextEditor(document) {
    let editor;

    return {
        ready: false,
        bold: false,
        listStyle: '',
        async init() {
            const input = this.$refs.input;
            const originalValue = input.value;
            const [{ Editor }, { default: StarterKit }, { BulletList }] = await Promise.all([
                import('@tiptap/core'), import('@tiptap/starter-kit'), import('@tiptap/extension-list'),
            ]);
            const DashList = BulletList.extend({
                name: 'dashList',
                priority: 1000,
                parseHTML() { return [{ tag: 'ul[data-list-style="dash"]' }]; },
                renderHTML() { return ['ul', { 'data-list-style': 'dash' }, 0]; },
                addInputRules() { return []; },
                addKeyboardShortcuts() { return {}; },
            });
            const content = input.value === originalValue ? editorDocument(document) : {
                type: 'doc',
                content: input.value.split('\n').map(line => ({
                    type: 'paragraph', content: line ? [{ type: 'text', text: line }] : [],
                })),
            };
            editor = new Editor({
                element: this.$refs.editor,
                content,
                extensions: [
                    StarterKit.configure({
                        blockquote: false, code: false, codeBlock: false, heading: false,
                        horizontalRule: false, italic: false, link: false, orderedList: false,
                        strike: false, underline: false, trailingNode: false,
                    }),
                    DashList,
                ],
                editorProps: {
                    attributes: {
                        id: input.id + '-editor',
                        role: 'textbox',
                        'aria-multiline': 'true',
                        'aria-labelledby': input.id + '-label',
                        'aria-describedby': input.getAttribute('aria-describedby') ?? '',
                        class: 'scholarship-rich-text',
                    },
                },
                onUpdate: () => {
                    input.value = editor.isEmpty ? '' : prefix + JSON.stringify(editor.getJSON());
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                },
                onSelectionUpdate: () => this.updateToolbar(),
                onTransaction: () => this.updateToolbar(),
            });
            this.ready = true;
        },
        updateToolbar() {
            if (!editor) return;
            this.bold = editor.isActive('bold');
            this.listStyle = editor.isActive('dashList') ? 'dash' : editor.isActive('bulletList') ? 'bullet' : '';
        },
        toggleBold() {
            editor.chain().focus().toggleBold().run();
        },
        toggleList(style) {
            editor.chain().focus().toggleList(style === 'dash' ? 'dashList' : 'bulletList', 'listItem').run();
        },
        destroy() {
            editor?.destroy();
        },
    };
}
