<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function __construct(protected CommentService $comments) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Comment::class);

        $status = CommentStatus::tryFrom((string) $request->query('status', 'pending'));

        return view('admin.comments.index', [
            'status' => $status,
            'comments' => Comment::query()
                ->when($status, fn ($query) => $query->where('status', $status))
                ->with(['post', 'user'])
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'pendingCount' => Comment::pending()->count(),
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $this->comments->approve($comment);

        return back()->with('status', 'Comment approved.');
    }

    public function spam(Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $this->comments->markSpam($comment);

        return back()->with('status', 'Comment marked as spam.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }
}
