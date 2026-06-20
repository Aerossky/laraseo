@props(['content' => []])

@php($toc = app(\App\Services\EditorJsRenderer::class)->tableOfContents($content))

@if ((string) $toc !== '')
    <nav aria-label="Table of contents"
        class="my-8 rounded-xl border border-gray-200 bg-gray-50 p-5 shadow-sm sm:p-6">
        <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            On this page
        </p>
        {!! $toc !!}
    </nav>
@endif
