<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Media Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Upload --}}
            <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data"
                    class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <div class="grow">
                        <label for="file" class="block text-sm font-medium text-gray-700">Upload image</label>
                        <input type="file" name="file" id="file" required accept="image/jpeg,image/png,image/webp,image/gif"
                            class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200" />
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, WEBP or GIF — max 5MB.</p>
                        @error('file')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Upload
                    </button>
                </form>
            </div>

            {{-- Grid --}}
            @if ($media->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center text-sm text-gray-500">
                    No images yet. Upload your first image above.
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($media as $item)
                        <div class="flex flex-col overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="aspect-video bg-gray-50">
                                <img src="{{ $item->getUrl() }}" alt="{{ $item->getCustomProperty('alt', '') }}"
                                    loading="lazy" class="h-full w-full object-cover" />
                            </div>

                            <div class="flex grow flex-col gap-3 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-gray-700" title="{{ $item->file_name }}">
                                        {{ $item->file_name }}
                                    </p>
                                    @if ($item->getCustomProperty('alt'))
                                        <span class="shrink-0 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">alt set</span>
                                    @else
                                        <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">no alt</span>
                                    @endif
                                </div>

                                {{-- Alt text --}}
                                <form method="POST" action="{{ route('admin.media.update', $item) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="alt" value="{{ $item->getCustomProperty('alt', '') }}"
                                        placeholder="Describe this image…" maxlength="255"
                                        class="w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
                                    <button type="submit"
                                        class="shrink-0 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Save
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                                    class="mt-auto flex items-center justify-between"
                                    onsubmit="return confirm('Delete this image?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-sm font-medium text-red-600 hover:text-red-700">
                                        Delete
                                    </button>
                                    <button type="submit" name="force" value="1"
                                        onclick="return confirm('This image may be used in posts. Delete anyway?')"
                                        class="text-xs text-gray-400 hover:text-red-600">
                                        Force delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
