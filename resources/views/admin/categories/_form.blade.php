@php
    $isEdit = (bool) $category;
    $action = $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
            <p class="font-medium">Please fix the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $category?->name) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-lg focus:border-gray-500 focus:ring-gray-500" />
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <label for="slug" class="mt-4 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $category?->slug) }}"
                    placeholder="Auto-generated from the name"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <label for="description" class="mt-4 block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Shown on the category archive page and used as a meta description fallback."
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">{{ old('description', $category?->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                @include('admin.partials.seo-fields', ['seoModel' => $category])
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                <button type="submit"
                    class="w-full rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    {{ $isEdit ? 'Update category' : 'Save category' }}
                </button>

                @if ($isEdit)
                    <a href="{{ route('admin.categories.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700">
                        Cancel
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>
