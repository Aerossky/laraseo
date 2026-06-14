<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
        <a href="{{ route('admin.posts.create') }}"
            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            New post
        </a>
    </x-slot>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach (['Total posts' => $totalPosts, 'Published' => $publishedPosts, 'Drafts' => $draftPosts] as $label => $value)
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-800">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-gray-800">Recent posts</h2>
            <a href="{{ route('admin.posts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">View all</a>
        </div>

        @forelse ($recentPosts as $post)
            <a href="{{ route('admin.posts.edit', $post) }}"
                class="flex items-center justify-between gap-4 border-b border-gray-50 px-6 py-3 hover:bg-gray-50">
                <span class="truncate text-sm font-medium text-gray-700">{{ $post->title }}</span>
                <span class="flex shrink-0 items-center gap-3 text-xs text-gray-400">
                    <span>{{ $post->category?->name ?? 'Uncategorized' }}</span>
                    <x-post-status :status="$post->status" />
                </span>
            </a>
        @empty
            <p class="px-6 py-8 text-center text-sm text-gray-500">No posts yet.</p>
        @endforelse
    </div>
</x-admin-layout>
