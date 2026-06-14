@props(['post'])

@php($featured = $post->getFirstMedia('featured'))

<article class="group">
    <a href="{{ route('blog.show', $post) }}" class="block">
        @if ($featured)
            <img src="{{ $featured->getUrl() }}" alt="{{ $featured->getCustomProperty('alt', '') }}"
                loading="lazy" class="mb-4 aspect-video w-full rounded-lg object-cover" />
        @endif

        <div class="flex items-center gap-3 text-xs text-gray-500">
            @if ($post->category)
                <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">
                    {{ $post->category->name }}
                </span>
            @endif
            <time datetime="{{ $post->published_at?->toDateString() }}">
                {{ $post->published_at?->format('M j, Y') }}
            </time>
        </div>

        <h2 class="mt-2 text-xl font-semibold text-gray-900 group-hover:text-gray-700">
            {{ $post->title }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">{{ $post->getExcerpt() }}</p>
    </a>
</article>
