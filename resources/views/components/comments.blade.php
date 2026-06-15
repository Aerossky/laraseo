@props(['post'])

@php($comments = $post->approvedComments)

<section id="comments" class="mt-16 border-t border-gray-100 pt-10">
    <h2 class="text-2xl font-bold text-gray-900">
        {{ $comments->count() }} {{ Str::plural('Comment', $comments->count()) }}
    </h2>

    @if ($comments->isNotEmpty())
        <ul class="mt-8 space-y-8">
            @foreach ($comments as $comment)
                <li class="flex gap-4">
                    <div aria-hidden="true"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500">
                        {{ Str::upper(Str::substr($comment->authorName(), 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-900">{{ $comment->authorName() }}</span>
                            <time datetime="{{ $comment->created_at->toIso8601String() }}" class="text-gray-400">
                                {{ $comment->created_at->format('F j, Y') }}
                            </time>
                        </div>
                        <p class="mt-1 whitespace-pre-line text-gray-700">{{ $comment->body }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="mt-4 text-sm text-gray-500">Be the first to comment.</p>
    @endif

    <div class="mt-12">
        <h3 class="text-lg font-semibold text-gray-900">Leave a comment</h3>

        @if (session('status'))
            <div class="mt-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="mt-4 space-y-4">
            @csrf

            @guest
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="author_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="author_name" id="author_name" value="{{ old('author_name') }}"
                            required maxlength="80"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500" />
                        @error('author_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="author_email" class="block text-sm font-medium text-gray-700">Email
                            <span class="text-gray-400">(not published)</span></label>
                        <input type="email" name="author_email" id="author_email" value="{{ old('author_email') }}"
                            required maxlength="255"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500" />
                        @error('author_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">Commenting as <span class="font-medium text-gray-700">{{ auth()->user()->name }}</span>.</p>
            @endguest

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Comment</label>
                <textarea name="body" id="body" rows="4" required maxlength="2000"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Honeypot: hidden from real users; bots that fill it are rejected --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
            </div>

            <p class="text-xs text-gray-400">Comments are reviewed before they appear.</p>

            <button type="submit"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                Post comment
            </button>
        </form>
    </div>
</section>
