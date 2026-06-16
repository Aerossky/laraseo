@php
    $isEdit = (bool) $user;
    $action = $isEdit ? route('admin.users.update', $user) : route('admin.users.store');
@endphp

<form method="POST" action="{{ $action }}" class="max-w-xl space-y-6">
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

    <div class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $user?->name) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $user?->email) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
            <select id="role" name="role" required
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </select>
            @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-400">
                Admin: full access · Editor: all content + moderation · Author: own posts only.
            </p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Password @unless($isEdit) <span class="text-red-500">*</span> @endunless
            </label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                @unless($isEdit) required @endunless
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @if ($isEdit)
                <p class="mt-1 text-xs text-gray-400">Leave blank to keep the current password.</p>
            @endif
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                @unless($isEdit) required @endunless
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit"
            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
            {{ $isEdit ? 'Update user' : 'Create user' }}
        </button>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</form>
