<x-public-layout>
    <x-breadcrumb :items="[
        ['name' => 'Blog', 'url' => route('blog.index')],
        ['name' => $category->name, 'url' => route('blog.category', $category)],
    ]" />

    <header class="mb-8">
        {{-- Category name is the page H1 --}}
        <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="mt-2 text-gray-600">{{ $category->description }}</p>
        @endif
    </header>

    @forelse ($posts as $post)
        <div class="mb-10">
            <x-post-card :post="$post" />
        </div>
    @empty
        <p class="text-gray-500">No posts in this category yet.</p>
    @endforelse

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</x-public-layout>
