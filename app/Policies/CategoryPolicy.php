<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Categories are managed by admins and editors only.
     */
    public function viewAny(User $user): bool
    {
        return $user->managesContent();
    }

    public function create(User $user): bool
    {
        return $user->managesContent();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->managesContent();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->managesContent();
    }
}
