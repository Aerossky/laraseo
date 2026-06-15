<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Every panel user can see the posts section; authors see only their own
     * rows (filtered in the controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Content managers may edit any post; authors only their own.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->managesContent() || $post->author_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}
