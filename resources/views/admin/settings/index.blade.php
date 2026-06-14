<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Settings</h1>
    </x-slot>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

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

        {{-- Site --}}
        <div class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-800">Site</h2>

            <div>
                <label for="site_name" class="block text-sm font-medium text-gray-700">Site name <span class="text-red-500">*</span></label>
                <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
                @error('site_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- SEO defaults --}}
        <div class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-800">SEO defaults</h2>

            <div>
                <label for="meta_title_format" class="block text-sm font-medium text-gray-700">Meta title format</label>
                <input type="text" id="meta_title_format" name="meta_title_format"
                    value="{{ old('meta_title_format', $settings['meta_title_format']) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm focus:border-gray-500 focus:ring-gray-500" />
                <p class="mt-1 text-xs text-gray-400">
                    Use <span class="font-mono">{title}</span> for the page title and <span class="font-mono">{site}</span> for the site name.
                </p>
                @error('meta_title_format') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="meta_description_fallback" class="block text-sm font-medium text-gray-700">Meta description fallback</label>
                <textarea id="meta_description_fallback" name="meta_description_fallback" rows="2"
                    placeholder="Used when a page has no meta description of its own."
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">{{ old('meta_description_fallback', $settings['meta_description_fallback']) }}</textarea>
                @error('meta_description_fallback') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Verification & scripts --}}
        <div class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-800">Verification &amp; scripts</h2>

            <div>
                <label for="google_site_verification" class="block text-sm font-medium text-gray-700">Google site verification</label>
                <input type="text" id="google_site_verification" name="google_site_verification"
                    value="{{ old('google_site_verification', $settings['google_site_verification']) }}"
                    placeholder="The content value of the google-site-verification meta tag"
                    class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm focus:border-gray-500 focus:ring-gray-500" />
                @error('google_site_verification') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="head_scripts" class="block text-sm font-medium text-gray-700">Head scripts</label>
                <textarea id="head_scripts" name="head_scripts" rows="4"
                    placeholder="Injected before &lt;/head&gt; — analytics, verification, etc."
                    class="mt-1 block w-full rounded-md border-gray-300 font-mono text-xs focus:border-gray-500 focus:ring-gray-500">{{ old('head_scripts', $settings['head_scripts']) }}</textarea>
                @error('head_scripts') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="body_scripts" class="block text-sm font-medium text-gray-700">Body scripts</label>
                <textarea id="body_scripts" name="body_scripts" rows="4"
                    placeholder="Injected after &lt;body&gt; — chat widgets, pixels, etc."
                    class="mt-1 block w-full rounded-md border-gray-300 font-mono text-xs focus:border-gray-500 focus:ring-gray-500">{{ old('body_scripts', $settings['body_scripts']) }}</textarea>
                @error('body_scripts') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- robots.txt --}}
        <div class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-800">robots.txt</h2>

            <div>
                <label for="robots_txt" class="block text-sm font-medium text-gray-700">Contents</label>
                <textarea id="robots_txt" name="robots_txt" rows="6"
                    class="mt-1 block w-full rounded-md border-gray-300 font-mono text-xs focus:border-gray-500 focus:ring-gray-500">{{ old('robots_txt', $settings['robots_txt']) }}</textarea>
                @error('robots_txt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <button type="submit"
                class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                Save settings
            </button>
        </div>
    </form>
</x-admin-layout>
