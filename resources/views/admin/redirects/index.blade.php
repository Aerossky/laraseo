<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Redirects</h1>
        <a href="{{ route('admin.redirects.create') }}"
            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            New redirect
        </a>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @if ($redirects->isEmpty())
            <p class="px-6 py-12 text-center text-sm text-gray-500">
                No redirects yet. <a href="{{ route('admin.redirects.create') }}" class="text-gray-800 underline">Create one</a>.
            </p>
        @else
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-medium">From</th>
                        <th class="px-6 py-3 font-medium">To</th>
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Active</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($redirects as $redirect)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.redirects.edit', $redirect) }}" class="font-mono text-gray-800 hover:underline">
                                    {{ $redirect->from_url }}
                                </a>
                            </td>
                            <td class="px-6 py-3 font-mono text-gray-600">{{ $redirect->to_url }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $redirect->type->value }}</td>
                            <td class="px-6 py-3">
                                <form method="POST" action="{{ route('admin.redirects.toggle', $redirect) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $redirect->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ $redirect->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.redirects.edit', $redirect) }}" class="text-gray-500 hover:text-gray-800">Edit</a>
                                    <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}"
                                        onsubmit="return confirm('Delete this redirect?')">
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
        {{ $redirects->links() }}
    </div>
</x-admin-layout>
