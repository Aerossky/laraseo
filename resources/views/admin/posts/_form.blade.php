@php
    $isEdit = (bool) $post;
    $action = $isEdit ? route('admin.posts.update', $post) : route('admin.posts.store');
    $contentJson = old('content')
        ? (is_array(old('content')) ? json_encode(old('content')) : old('content'))
        : ($post?->content ? json_encode($post->content) : '{}');
    $featured = $post?->getFirstMedia('featured');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
            <p class="font-medium">Please fix the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <label for="title" class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $post?->title) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-lg focus:border-gray-500 focus:ring-gray-500" />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <label for="slug" class="mt-4 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $post?->slug) }}"
                    placeholder="Auto-generated from the title"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <label class="block text-sm font-medium text-gray-700">Content</label>
                <input type="hidden" id="content-input" name="content" value="{{ $contentJson }}" />
                <div data-editor data-editor-input="#content-input"
                    class="prose mt-2 min-h-64 max-w-none rounded-md border border-gray-200 px-4 py-2"></div>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                @include('admin.partials.seo-fields')
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="space-y-4 rounded-lg bg-white p-6 shadow-sm" x-data="{ status: @js(old('status', $post?->status?->value ?? 'draft')) }">
                <h3 class="text-sm font-semibold text-gray-800">Publish</h3>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" x-model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                        @foreach (\App\Enums\PostStatus::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div x-show="status === 'scheduled'" x-cloak>
                    <label for="published_at" class="block text-sm font-medium text-gray-700">Publish at</label>
                    <input type="datetime-local" id="published_at" name="published_at"
                        value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
                    @error('published_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="show_toc" value="1"
                        @checked(old('show_toc', $post?->show_toc ?? true))
                        class="rounded border-gray-300 text-gray-800 focus:ring-gray-500" />
                    Show table of contents
                </label>

                <button type="submit"
                    class="w-full rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    {{ $isEdit ? 'Update post' : 'Save post' }}
                </button>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="category_id" name="category_id"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">Uncategorized</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Required before publishing.</p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <label class="block text-sm font-medium text-gray-700">Featured image</label>
                @if ($featured)
                    <img src="{{ $featured->getUrl() }}" alt="{{ $featured->getCustomProperty('alt', '') }}"
                        loading="lazy" class="mt-2 aspect-video w-full rounded-md object-cover" />
                @endif
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif"
                    class="mt-2 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm hover:file:bg-gray-200" />
                @error('featured_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <label for="featured_alt" class="mt-3 block text-sm font-medium text-gray-700">Featured image alt</label>
                <input type="text" id="featured_alt" name="featured_alt"
                    value="{{ old('featured_alt', $featured?->getCustomProperty('alt', '')) }}" maxlength="255"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
            </div>

            @if ($isEdit)
                <a href="{{ route('admin.posts.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700">
                    Cancel
                </a>
            @endif
        </div>
    </div>
</form>

@push('scripts')
    @vite('resources/js/editor.js')
@endpush
