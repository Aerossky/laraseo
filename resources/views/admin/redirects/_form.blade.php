@php
    $isEdit = (bool) $redirect;
    $action = $isEdit ? route('admin.redirects.update', $redirect) : route('admin.redirects.store');
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

    <div class="max-w-2xl space-y-5 rounded-lg bg-white p-6 shadow-sm">
        <div>
            <label for="from_url" class="block text-sm font-medium text-gray-700">From <span class="text-red-500">*</span></label>
            <input type="text" id="from_url" name="from_url" value="{{ old('from_url', $redirect?->from_url) }}" required
                placeholder="/old-page"
                class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm focus:border-gray-500 focus:ring-gray-500" />
            <p class="mt-1 text-xs text-gray-400">A local path to catch, e.g. <span class="font-mono">/old-page</span>.</p>
            @error('from_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="to_url" class="block text-sm font-medium text-gray-700">To <span class="text-red-500">*</span></label>
            <input type="text" id="to_url" name="to_url" value="{{ old('to_url', $redirect?->to_url) }}" required
                placeholder="/new-page or https://example.com"
                class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm focus:border-gray-500 focus:ring-gray-500" />
            <p class="mt-1 text-xs text-gray-400">A local path or a full URL.</p>
            @error('to_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="type" name="type"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                    @foreach (\App\Enums\RedirectType::cases() as $case)
                        <option value="{{ $case->value }}"
                            @selected((int) old('type', $redirect?->type->value ?? \App\Enums\RedirectType::Permanent->value) === $case->value)>
                            {{ $case->label() }}
                        </option>
                    @endforeach
                </select>
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1"
                        @checked(old('is_active', $redirect?->is_active ?? true))
                        class="rounded border-gray-300 text-gray-800 focus:ring-gray-500" />
                    Active
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                {{ $isEdit ? 'Update redirect' : 'Save redirect' }}
            </button>
            <a href="{{ route('admin.redirects.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </div>
</form>
