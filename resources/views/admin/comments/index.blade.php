<x-admin-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Comments</h1>
    </x-slot>

    @php($tabs = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'spam' => 'Spam',
        '' => 'All',
    ])

    <div class="mb-6 flex gap-1 text-sm">
        @foreach ($tabs as $value => $label)
            @php($active = ($status->value ?? '') === $value)
            <a href="{{ route('admin.comments.index', $value !== '' ? ['status' => $value] : []) }}"
                class="rounded-md px-3 py-1.5 font-medium {{ $active ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @if ($comments->isEmpty())
            <p class="px-6 py-12 text-center text-sm text-gray-500">No comments here.</p>
        @else
            <ul class="divide-y divide-gray-50">
                @foreach ($comments as $comment)
                    <li class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="font-semibold text-gray-900">{{ $comment->authorName() }}</span>
                                    @if ($comment->user)
                                        <span class="rounded bg-blue-50 px-1.5 py-0.5 text-xs font-medium text-blue-700">User</span>
                                    @else
                                        <span class="text-gray-400">{{ $comment->author_email }}</span>
                                    @endif
                                    <span class="text-gray-300">·</span>
                                    <time class="text-gray-400" datetime="{{ $comment->created_at->toIso8601String() }}">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $comment->body }}</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    on
                                    <a href="{{ route('blog.show', $comment->post) }}" target="_blank"
                                        class="underline hover:text-gray-600">{{ $comment->post->title }}</a>
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-2 text-xs">
                                @if ($comment->status !== \App\Enums\CommentStatus::Approved)
                                    <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="rounded-md bg-green-100 px-2.5 py-1 font-medium text-green-800 hover:bg-green-200">Approve</button>
                                    </form>
                                @endif
                                @if ($comment->status !== \App\Enums\CommentStatus::Spam)
                                    <form method="POST" action="{{ route('admin.comments.spam', $comment) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="rounded-md bg-gray-100 px-2.5 py-1 font-medium text-gray-600 hover:bg-gray-200">Spam</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                                    onsubmit="return confirm('Delete this comment permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-md px-2.5 py-1 font-medium text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-4">
        {{ $comments->links() }}
    </div>
</x-admin-layout>
