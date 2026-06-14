{{-- Reusable SEO panel. Pass :seoModel for any HasSeoMeta model; defaults to $post. --}}
@php($seo = ($seoModel ?? $post ?? null)?->seoMeta)

<div class="space-y-5">
    <h3 class="text-sm font-semibold text-gray-800">SEO</h3>

    {{-- Meta title --}}
    <div x-data="{ value: @js(old('seo.meta_title', $seo?->meta_title) ?? ''), limit: 60 }">
        <div class="flex items-center justify-between">
            <label for="seo_meta_title" class="block text-sm font-medium text-gray-700">Meta title</label>
            <span class="text-xs" :class="value.length > limit ? 'text-red-600' : 'text-gray-400'"
                x-text="`${value.length}/${limit}`"></span>
        </div>
        <input type="text" id="seo_meta_title" name="seo[meta_title]" x-model="value" maxlength="255"
            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
        <p class="mt-1 text-xs text-gray-400">Leave blank to use the post title.</p>
        @error('seo.meta_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Meta description --}}
    <div x-data="{ value: @js(old('seo.meta_description', $seo?->meta_description) ?? ''), limit: 160 }">
        <div class="flex items-center justify-between">
            <label for="seo_meta_description" class="block text-sm font-medium text-gray-700">Meta description</label>
            <span class="text-xs" :class="value.length > limit ? 'text-red-600' : 'text-gray-400'"
                x-text="`${value.length}/${limit}`"></span>
        </div>
        <textarea id="seo_meta_description" name="seo[meta_description]" x-model="value" rows="2" maxlength="255"
            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500"></textarea>
        <p class="mt-1 text-xs text-gray-400">Leave blank to use the excerpt.</p>
        @error('seo.meta_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        {{-- OG title --}}
        <div>
            <label for="seo_og_title" class="block text-sm font-medium text-gray-700">OG title</label>
            <input type="text" id="seo_og_title" name="seo[og_title]" value="{{ old('seo.og_title', $seo?->og_title) }}"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
        </div>

        {{-- OG image --}}
        <div>
            <label for="seo_og_image" class="block text-sm font-medium text-gray-700">OG image URL</label>
            <input type="url" id="seo_og_image" name="seo[og_image]" value="{{ old('seo.og_image', $seo?->og_image) }}"
                placeholder="Defaults to the featured image"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
            @error('seo.og_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- OG description --}}
    <div>
        <label for="seo_og_description" class="block text-sm font-medium text-gray-700">OG description</label>
        <textarea id="seo_og_description" name="seo[og_description]" rows="2"
            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">{{ old('seo.og_description', $seo?->og_description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        {{-- Canonical --}}
        <div>
            <label for="seo_canonical_url" class="block text-sm font-medium text-gray-700">Canonical URL</label>
            <input type="url" id="seo_canonical_url" name="seo[canonical_url]"
                value="{{ old('seo.canonical_url', $seo?->canonical_url) }}"
                placeholder="Defaults to the post URL"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500" />
            @error('seo.canonical_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Robots --}}
        <div>
            <label for="seo_robots" class="block text-sm font-medium text-gray-700">Robots</label>
            <select id="seo_robots" name="seo[robots]"
                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                @foreach (\App\Http\Requests\StorePostRequest::ROBOTS as $robots)
                    <option value="{{ $robots }}" @selected(old('seo.robots', $seo?->robots ?? 'index, follow') === $robots)>
                        {{ $robots }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
