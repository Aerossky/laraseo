<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'laraseo') }} · Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="border-b border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-gray-800">
                            laraseo
                        </a>
                        <div class="hidden gap-1 sm:flex">
                            @php
                                $nav = [
                                    'admin.dashboard' => 'Dashboard',
                                    'admin.posts.index' => 'Posts',
                                    'admin.categories.index' => 'Categories',
                                    'admin.media.index' => 'Media',
                                    'admin.redirects.index' => 'Redirects',
                                    'admin.settings.index' => 'Settings',
                                ];
                            @endphp
                            @foreach ($nav as $route => $label)
                                @php $pattern = str_replace('.index', '.*', $route); @endphp
                                <a href="{{ route($route) }}"
                                    class="inline-flex items-center border-b-2 px-3 pt-1 text-sm font-medium {{ request()->routeIs($pattern) ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        @if (Route::has('blog.index'))
                            <a href="{{ route('blog.index') }}" target="_blank"
                                class="text-sm text-gray-500 hover:text-gray-700">View site ↗</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        @isset($header)
            <header class="bg-white shadow-sm">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>

</html>
