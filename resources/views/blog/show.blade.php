@php($featured = $post->getFirstMedia('featured'))

<x-public-layout>
    <x-breadcrumb :items="[
        ['name' => 'Blog', 'url' => route('blog.index')],
        $post->category
            ? ['name' => $post->category->name, 'url' => route('blog.category', $post->category)]
            : ['name' => 'Uncategorized', 'url' => route('blog.index')],
        ['name' => $post->title, 'url' => route('blog.show', $post)],
    ]" />

    <article>
        <header class="mb-8">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                @if ($post->category)
                    <a href="{{ route('blog.category', $post->category) }}"
                        class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600 hover:bg-gray-200">
                        {{ $post->category->name }}
                    </a>
                @endif
                <time datetime="{{ $post->published_at?->toDateString() }}">
                    {{ $post->published_at?->format('F j, Y') }}
                </time>
            </div>

            {{-- The single H1 on the page is always the post title (SEO rule) --}}
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-gray-900">{{ $post->title }}</h1>
        </header>

        @if ($featured)
            <img src="{{ $featured->getUrl() }}" alt="{{ $featured->getCustomProperty('alt', '') }}"
                loading="lazy" class="mb-8 aspect-video w-full rounded-xl object-cover" />
        @endif

        <x-post-content :blocks="$post->content" />
    </article>
</x-public-layout>
