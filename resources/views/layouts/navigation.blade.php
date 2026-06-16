@php
    /**
     * Admin navigation — single source of truth.
     *
     * Each entry is either a single link or a dropdown group:
     *   - Link:  ['label' => 'Settings', 'route' => 'admin.settings.index']
     *   - Group: ['label' => 'Blog', 'children' => [ ...links... ]]
     *
     * To add your own top-level page, register its route under the `admin.`
     * prefix in routes/web.php and add one entry here. Index routes use the
     * `.index` suffix so the active-state matcher highlights every nested page
     * (create, edit, …) under that section.
     */
    $user = Auth::user();
    $managesContent = $user->managesContent();
    $managesSite = $user->managesSite();

    // Menu is filtered by role: authors see only Posts + Media; editors add
    // content management; admins add site configuration and user management.
    $nav = array_values(array_filter([
        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
        ['label' => 'Blog', 'children' => array_values(array_filter([
            ['label' => 'Posts', 'route' => 'admin.posts.index'],
            $managesContent ? ['label' => 'Categories', 'route' => 'admin.categories.index'] : null,
            ['label' => 'Media', 'route' => 'admin.media.index'],
        ]))],
        $managesContent ? ['label' => 'Comments', 'route' => 'admin.comments.index'] : null,
        $managesSite ? ['label' => 'Redirects', 'route' => 'admin.redirects.index'] : null,
        $managesSite ? ['label' => 'Users', 'route' => 'admin.users.index'] : null,
        $managesSite ? ['label' => 'Settings', 'route' => 'admin.settings.index'] : null,
    ]));

    // Pending comments awaiting moderation — only relevant to moderators.
    $pendingComments = $managesContent ? \App\Models\Comment::pending()->count() : 0;

    // A link is active when its route (or any nested page under it) matches.
    $isActive = fn (string $route): bool => request()->routeIs(str_replace('.index', '.*', $route));
@endphp

<nav class="border-b border-gray-100 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-gray-800">
                    {{ config('app.name', 'laraseo') }}
                </a>

                <div class="hidden gap-1 sm:flex">
                    @foreach ($nav as $item)
                        @if (isset($item['children']))
                            @php $groupActive = collect($item['children'])->contains(fn ($child) => $isActive($child['route'])); @endphp
                            <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
                                <button type="button" @click="open = ! open" :aria-expanded="open"
                                    class="inline-flex h-16 items-center gap-1 border-b-2 px-3 text-sm font-medium {{ $groupActive ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                    {{ $item['label'] }}
                                    <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="open" x-cloak @click.outside="open = false"
                                    x-transition.origin.top.left
                                    class="absolute left-0 z-50 mt-0 w-44 rounded-md border border-gray-100 bg-white py-1 shadow-lg">
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ route($child['route']) }}"
                                            class="block px-4 py-2 text-sm {{ $isActive($child['route']) ? 'bg-gray-50 font-medium text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route($item['route']) }}"
                                class="inline-flex items-center gap-1.5 border-b-2 px-3 pt-1 text-sm font-medium {{ $isActive($item['route']) ? 'border-gray-800 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                                {{ $item['label'] }}
                                @if ($item['route'] === 'admin.comments.index' && $pendingComments > 0)
                                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white">{{ $pendingComments }}</span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('blog.index'))
                    <a href="{{ route('blog.index') }}" target="_blank"
                        class="text-sm text-gray-500 hover:text-gray-700">View site ↗</a>
                @endif
                <a href="{{ route('profile.edit') }}"
                    class="text-sm font-medium {{ request()->routeIs('profile.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ Auth::user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Log out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
