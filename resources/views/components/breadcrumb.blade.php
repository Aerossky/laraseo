@props(['items' => []])

@php($schema = app(\App\Seo\SchemaBuilder::class))

@if (count($items) > 1)
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach ($items as $item)
                <li class="flex items-center gap-2">
                    @if (! $loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-gray-700">{{ $item['name'] }}</a>
                        <span aria-hidden="true">/</span>
                    @else
                        <span class="text-gray-700" aria-current="page">{{ $item['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- BreadcrumbList JSON-LD (FR-23) --}}
    {!! $schema->toScript($schema->breadcrumbs($items)) !!}
@endif
