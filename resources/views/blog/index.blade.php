<x-public-layout>
    <h1 class="mb-8 text-3xl font-bold text-gray-900">Blog</h1>

    @forelse ($posts as $post)
        <div class="mb-10">
            <x-post-card :post="$post" />
        </div>
    @empty
        <p class="text-gray-500">No posts published yet.</p>
    @endforelse

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</x-public-layout>
