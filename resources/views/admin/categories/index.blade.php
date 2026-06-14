<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Categories</h1>
        <a href="{{ route('admin.categories.create') }}"
            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            New category
        </a>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @if ($categories->isEmpty())
            <p class="px-6 py-12 text-center text-sm text-gray-500">
                No categories yet. <a href="{{ route('admin.categories.create') }}" class="text-gray-800 underline">Create one</a>.
            </p>
        @else
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-medium">Name</th>
                        <th class="px-6 py-3 font-medium">Posts</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-gray-800 hover:underline">
                                    {{ $category->name }}
                                </a>
                                <p class="text-xs text-gray-400">/{{ $category->slug }}</p>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $category->posts_count }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-gray-500 hover:text-gray-800">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                        onsubmit="return confirm('Delete this category? Its posts will become uncategorized.')">
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
        {{ $categories->links() }}
    </div>
</x-admin-layout>
