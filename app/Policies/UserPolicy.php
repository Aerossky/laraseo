<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * User administration is restricted to admins.
     */
    public function viewAny(User $user): bool
    {
        return $user->managesSite();
    }

    public function create(User $user): bool
    {
        return $user->managesSite();
    }

    public function update(User $user, User $model): bool
    {
        return $user->managesSite();
    }

    /**
     * Admins may delete other users, but never their own account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->managesSite() && $user->id !== $model->id;
    }
}
