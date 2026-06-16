/**
 * EditorJS block tool: insert an image picked from the media library.
 *
 * Emits the same data shape as @editorjs/image ({ file: { url, alt }, caption })
 * so the server-side EditorJsRenderer renders it through the existing image path.
 * The caption doubles as the required alt text — matching the upload tool — so
 * the publish-time alt-text gate applies here too.
 */
export default class LibraryImage {
    static get toolbox() {
        return {
            title: 'Library image',
            icon: '<svg width="17" height="15" viewBox="0 0 336 276" xmlns="http://www.w3.org/2000/svg"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 72 56-29 42 30zm0 52l-43-30-56 30-81-67-66 39v23c0 19 15 34 34 34h178c17 0 31-13 34-29zM79 0h178c44 0 79 35 79 79v118c0 44-35 79-79 79H79c-44 0-79-35-79-79V79C0 35 35 0 79 0z"/></svg>',
        };
    }

    constructor({ data, config }) {
        this.data = {
            file: { url: data?.file?.url || '', alt: data?.file?.alt || '' },
            caption: data?.caption || '',
        };
        this.endpoint = config?.endpoint || '/admin/media/library';
        this.wrapper = null;
        this.captionEl = null;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.renderState();

        return this.wrapper;
    }

    renderState() {
        this.wrapper.innerHTML = '';

        if (!this.data.file.url) {
            this.wrapper.appendChild(this.button('Choose from library', () => this.openPicker()));

            return;
        }

        const img = document.createElement('img');
        img.src = this.data.file.url;
        img.alt = this.data.file.alt;
        img.loading = 'lazy';
        img.className = 'w-full rounded-md';
        this.wrapper.appendChild(img);

        this.captionEl = document.createElement('div');
        this.captionEl.contentEditable = 'true';
        this.captionEl.dataset.placeholder = 'Add a caption / alt text…';
        this.captionEl.textContent = this.data.caption;
        this.captionEl.className = 'mt-2 text-sm text-gray-600 outline-none empty:before:text-gray-400 empty:before:content-[attr(data-placeholder)]';
        this.wrapper.appendChild(this.captionEl);

        this.wrapper.appendChild(this.button('Change image', () => this.openPicker(), 'mt-2'));
    }

    button(label, onClick, extra = '') {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = label;
        btn.className = `rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 ${extra}`.trim();
        btn.addEventListener('click', onClick);

        return btn;
    }

    async openPicker() {
        let items = [];

        try {
            const response = await fetch(this.endpoint, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            items = await response.json();
        } catch {
            items = [];
        }

        this.showModal(items);
    }

    showModal(items) {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';

        const backdrop = document.createElement('div');
        backdrop.className = 'absolute inset-0 bg-gray-900/50';
        backdrop.addEventListener('click', () => close());
        overlay.appendChild(backdrop);

        const panel = document.createElement('div');
        panel.className = 'relative flex max-h-[80vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg bg-white shadow-xl';
        overlay.appendChild(panel);

        const header = document.createElement('div');
        header.className = 'flex items-center justify-between border-b border-gray-200 px-6 py-4';
        header.innerHTML = '<h3 class="text-sm font-semibold text-gray-800">Choose from media library</h3>';
        const closeBtn = this.button('Close', () => close());
        header.appendChild(closeBtn);
        panel.appendChild(header);

        const body = document.createElement('div');
        body.className = 'overflow-y-auto p-6';
        panel.appendChild(body);

        const close = () => {
            document.removeEventListener('keydown', onKey);
            overlay.remove();
        };
        const onKey = (event) => {
            if (event.key === 'Escape') {
                close();
            }
        };
        document.addEventListener('keydown', onKey);

        if (!items.length) {
            body.innerHTML = '<p class="text-center text-sm text-gray-500">No images yet. Upload some in the Media Library.</p>';
        } else {
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4';

            items.forEach((item) => {
                const cell = document.createElement('button');
                cell.type = 'button';
                cell.className = 'group overflow-hidden rounded-md border border-gray-200 text-left hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500';
                cell.innerHTML = `<span class="block aspect-video bg-gray-50"><img src="${item.url}" alt="${item.alt}" loading="lazy" class="h-full w-full object-cover"></span>`;
                cell.addEventListener('click', () => {
                    this.select(item);
                    close();
                });
                grid.appendChild(cell);
            });

            body.appendChild(grid);
        }

        document.body.appendChild(overlay);
    }

    select(item) {
        this.data = {
            file: { url: item.url, alt: item.alt || '' },
            // Seed the caption with the library alt so the alt-text gate passes;
            // the editor can refine it afterwards.
            caption: item.alt || '',
        };
        this.renderState();
    }

    save() {
        const caption = this.captionEl ? this.captionEl.textContent.trim() : this.data.caption;

        return {
            file: { url: this.data.file.url, alt: caption },
            caption,
        };
    }

    validate(data) {
        return Boolean(data.file && data.file.url);
    }
}
