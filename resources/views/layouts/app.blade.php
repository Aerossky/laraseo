@php($siteName = \App\Models\Setting::get('site_name', config('app.name', 'laraseo')))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- All SEO head tags: title, description, canonical, OG, Twitter, JSON-LD (FR-66) --}}
    <x-seo-head />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Admin-managed head scripts, e.g. analytics (FR-72) --}}
    {!! \App\Models\Setting::get('head_scripts') !!}
</head>

<body class="min-h-screen bg-white font-sans text-gray-800 antialiased">
    {{-- Admin-managed body scripts (FR-73) --}}
    {!! \App\Models\Setting::get('body_scripts') !!}

    <header class="border-b border-gray-100">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-5">
            <a href="{{ url('/') }}" class="text-lg font-bold text-gray-900">{{ $siteName }}</a>
            <nav class="flex gap-6 text-sm text-gray-600">
                <a href="{{ route('blog.index') }}" class="hover:text-gray-900">Blog</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-10">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-gray-100">
        <div class="mx-auto max-w-3xl px-4 py-8 text-sm text-gray-500">
            &copy; {{ now()->year }} {{ $siteName }}
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
