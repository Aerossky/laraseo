@props(['for' => null])
@php($seo = app(\App\Seo\SeoService::class))
@if ($for)
    @php($seo->for($for))
@endif
{!! $seo->render() !!}
