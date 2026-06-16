<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Comment moderation is limited to admins and editors.
     */
    public function viewAny(User $user): bool
    {
        return $user->managesContent();
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->managesContent();
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->managesContent();
    }
}
