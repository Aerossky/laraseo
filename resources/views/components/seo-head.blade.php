@props(['for' => null])
@if ($for)
    @php(seo()->for($for))
@endif
{!! seo()->render() !!}
