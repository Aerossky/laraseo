<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Posts</h1>
        <a href="{{ route('admin.posts.create') }}"
            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            New post
        </a>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @if ($posts->isEmpty())
            <p class="px-6 py-12 text-center text-sm text-gray-500">
                No posts yet. <a href="{{ route('admin.posts.create') }}" class="text-gray-800 underline">Create one</a>.
            </p>
        @else
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-medium">Title</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Category</th>
                        @if (auth()->user()->managesContent())
                            <th class="px-6 py-3 font-medium">Author</th>
                        @endif
                        <th class="px-6 py-3 font-medium">Published</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($posts as $post)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-gray-800 hover:underline">
                                    {{ $post->title }}
                                </a>
                                <p class="text-xs text-gray-400">/{{ $post->slug }}</p>
                            </td>
                            <td class="px-6 py-3"><x-post-status :status="$post->status" /></td>
                            <td class="px-6 py-3 text-gray-600">{{ $post->category?->name ?? '—' }}</td>
                            @if (auth()->user()->managesContent())
                                <td class="px-6 py-3 text-gray-600">{{ $post->author?->name ?? '—' }}</td>
                            @endif
                            <td class="px-6 py-3 text-gray-500">
                                {{ $post->published_at?->format('M j, Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="text-gray-500 hover:text-gray-800">Edit</a>
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
                                        onsubmit="return confirm('Delete this post?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</x-admin-layout>
