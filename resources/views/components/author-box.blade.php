@props(['author' => null])

@if ($author)
    <aside class="mt-12 rounded-xl border border-gray-200 bg-gray-50 p-6">
        <div class="flex items-start gap-4">
            @if ($author->getAvatarUrl())
                <img src="{{ $author->getAvatarUrl() }}" alt="{{ $author->name }}"
                    loading="lazy" class="h-16 w-16 shrink-0 rounded-full object-cover" />
            @else
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xl font-semibold text-gray-500">
                    {{ \Illuminate\Support\Str::of($author->name)->substr(0, 1)->upper() }}
                </span>
            @endif

            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Written by</p>
                <p class="text-lg font-bold text-gray-900">{{ $author->name }}</p>

                @if ($author->bio)
                    <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $author->bio }}</p>
                @endif

                @if (count($author->socialLinks()))
                    <div class="mt-3 flex items-center gap-3">
                        @foreach ($author->socialLinks() as $type => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener nofollow"
                                aria-label="{{ ucfirst($type) }}"
                                class="text-gray-400 transition-colors hover:text-gray-900">
                                @switch($type)
                                    @case('twitter')
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        @break
                                    @case('linkedin')
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>
                                        @break
                                    @default
                                        {{-- website / globe --}}
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m-9 9h18"/></svg>
                                @endswitch
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </aside>
@endif
