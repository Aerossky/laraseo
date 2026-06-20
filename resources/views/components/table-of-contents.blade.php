@props(['content' => []])

@php($toc = app(\App\Services\EditorJsRenderer::class)->tableOfContents($content))

@if ((string) $toc !== '')
    <nav aria-label="Table of contents" class="mb-8 rounded-lg border border-gray-100 bg-gray-50 p-5 text-sm">
        <p class="mb-3 font-semibold text-gray-900">On this page</p>
        {!! $toc !!}
    </nav>
@endif
