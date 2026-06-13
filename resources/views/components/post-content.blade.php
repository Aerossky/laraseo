@props(['blocks' => []])

<div {{ $attributes->merge(['class' => 'prose max-w-none']) }}>
    {!! app(\App\Services\EditorJsRenderer::class)->render($blocks) !!}
</div>
