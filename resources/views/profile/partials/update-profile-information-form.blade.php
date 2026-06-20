<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="avatar" :value="__('Avatar')" />
            <div class="mt-2 flex items-center gap-4">
                @if ($user->getAvatarUrl())
                    <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}"
                        loading="lazy" class="h-16 w-16 rounded-full object-cover" />
                @else
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-lg font-semibold text-gray-500">
                        {{ \Illuminate\Support\Str::of($user->name)->substr(0, 1)->upper() }}
                    </span>
                @endif
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm hover:file:bg-gray-200" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea id="bio" name="bio" rows="3" maxlength="300"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="{{ __('A short bio shown on your articles.') }}">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="website" :value="__('Website')" />
                <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $user->website)" placeholder="https://" />
                <x-input-error class="mt-2" :messages="$errors->get('website')" />
            </div>
            <div>
                <x-input-label for="twitter" :value="__('X (Twitter)')" />
                <x-text-input id="twitter" name="twitter" type="url" class="mt-1 block w-full" :value="old('twitter', $user->twitter)" placeholder="https://x.com/" />
                <x-input-error class="mt-2" :messages="$errors->get('twitter')" />
            </div>
            <div>
                <x-input-label for="linkedin" :value="__('LinkedIn')" />
                <x-text-input id="linkedin" name="linkedin" type="url" class="mt-1 block w-full" :value="old('linkedin', $user->linkedin)" placeholder="https://linkedin.com/in/" />
                <x-input-error class="mt-2" :messages="$errors->get('linkedin')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
