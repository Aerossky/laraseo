<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(protected CommentService $comments) {}

    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->isPublished(), 404);

        $this->comments->create(
            $post,
            $request->validated(),
            $request->user(),
            $request->ip(),
        );

        return redirect()
            ->route('blog.show', $post)
            ->withFragment('comments')
            ->with('status', 'Thanks! Your comment is awaiting moderation.');
    }
}
