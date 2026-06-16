<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'author_name',
        'author_email',
        'body',
        'status',
        'ip_address',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The name to display — the registered user's name, or the guest's name.
     */
    public function authorName(): string
    {
        return $this->user?->name ?? (string) $this->author_name;
    }

    public function isApproved(): bool
    {
        return $this->status === CommentStatus::Approved;
    }

    /** @param Builder<Comment> $query */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', CommentStatus::Approved);
    }

    /** @param Builder<Comment> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', CommentStatus::Pending);
    }
}
