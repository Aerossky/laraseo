<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Users</h1>
        <a href="{{ route('admin.users.create') }}"
            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            New user
        </a>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Role</th>
                    <th class="px-6 py-3 font-medium">Posts</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($users as $user)
                    @php($roleColor = match ($user->role) {
                        \App\Enums\UserRole::Admin => 'bg-purple-100 text-purple-800',
                        \App\Enums\UserRole::Editor => 'bg-blue-100 text-blue-800',
                        \App\Enums\UserRole::Author => 'bg-gray-100 text-gray-600',
                    })
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-gray-800 hover:underline">
                                {{ $user->name }}
                            </a>
                            @if ($user->id === auth()->id())
                                <span class="ml-1 text-xs text-gray-400">(you)</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $roleColor }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $user->posts_count }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-500 hover:text-gray-800">Edit</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                        onsubmit="return confirm('Delete {{ $user->name }}? Their posts will be kept but unassigned.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-admin-layout>
