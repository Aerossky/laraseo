<?php

namespace App\Services;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentService
{
    /**
     * Store a new comment in the moderation queue (always Pending).
     *
     * @param  array{author_name?: ?string, author_email?: ?string, body: string}  $data
     */
    public function create(Post $post, array $data, ?User $author, ?string $ip): Comment
    {
        return $post->comments()->create([
            'user_id' => $author?->id,
            // Signed-in authors use their account identity, not the form fields.
            'author_name' => $author ? null : ($data['author_name'] ?? null),
            'author_email' => $author ? null : ($data['author_email'] ?? null),
            'body' => $data['body'],
            'status' => CommentStatus::Pending,
            'ip_address' => $ip,
        ]);
    }

    public function approve(Comment $comment): void
    {
        $comment->update([
            'status' => CommentStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function markSpam(Comment $comment): void
    {
        $comment->update([
            'status' => CommentStatus::Spam,
            'approved_at' => null,
        ]);
    }
}
