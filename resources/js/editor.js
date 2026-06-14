import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import ImageTool from '@editorjs/image';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import CodeTool from '@editorjs/code';
import Table from '@editorjs/table';

// NOTE: `editorjs-gallery` is intentionally not imported. Its published build
// is a webpack bundle (eval + css-loader runtime + .pcss) that breaks under
// Vite and crashes the whole editor. Gallery is deferred until a Vite-
// compatible package is chosen. The PHP renderer still supports gallery blocks.

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

// Headers so the upload endpoint passes CSRF and is treated as an API request
// (expectsJson) — see App\Http\Requests\StoreMediaRequest.
const uploadHeaders = () => ({
    'X-CSRF-TOKEN': csrfToken(),
    Accept: 'application/json',
});

const uploadConfig = () => ({
    field: 'file',
    endpoints: { byFile: '/admin/media/upload' },
    additionalRequestHeaders: uploadHeaders(),
});

/**
 * Create an EditorJS instance. Note: the Header tool is locked to H2/H3 —
 * H1 is reserved for the post title and must never be available in the editor.
 */
export function createEditor({ holder, data = {}, onChange = null } = {}) {
    return new EditorJS({
        holder,
        data,
        placeholder: 'Start writing your post…',
        tools: {
            header: {
                class: Header,
                inlineToolbar: true,
                config: {
                    levels: [2, 3],
                    defaultLevel: 2,
                },
            },
            image: {
                class: ImageTool,
                config: uploadConfig(),
            },
            list: {
                class: List,
                inlineToolbar: true,
                config: { defaultStyle: 'unordered' },
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
            },
            code: CodeTool,
            table: {
                class: Table,
                inlineToolbar: true,
            },
        },
        onChange: onChange
            ? async (api) => onChange(await api.saver.save())
            : undefined,
    });
}

// Auto-init: any [data-editor] element becomes an editor and syncs its JSON
// into the hidden input named by [data-editor-input].
function bootEditors() {
    document.querySelectorAll('[data-editor]').forEach((el) => {
        if (el.__editor) {
            return;
        }

        const input = el.dataset.editorInput
            ? document.querySelector(el.dataset.editorInput)
            : null;

        let initial = {};
        if (input?.value) {
            try {
                initial = JSON.parse(input.value);
            } catch {
                initial = {};
            }
        }

        el.__editor = createEditor({
            holder: el,
            data: initial,
            onChange: input
                ? (output) => {
                      input.value = JSON.stringify(output);
                  }
                : null,
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootEditors);
} else {
    bootEditors();
}
