@props(['status'])

@php
    $value = $status instanceof \App\Enums\PostStatus ? $status->value : (string) $status;
    $label = $status instanceof \App\Enums\PostStatus ? $status->label() : ucfirst($value);
    $colors = [
        'draft' => 'bg-gray-100 text-gray-600',
        'published' => 'bg-green-100 text-green-700',
        'scheduled' => 'bg-amber-100 text-amber-700',
    ][$value] ?? 'bg-gray-100 text-gray-600';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2 py-0.5 text-xs font-medium '.$colors]) }}>
    {{ $label }}
</span>
