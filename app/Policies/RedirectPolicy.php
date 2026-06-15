<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;

class RedirectPolicy
{
    /**
     * Redirects are site configuration — admins only.
     */
    public function viewAny(User $user): bool
    {
        return $user->managesSite();
    }

    public function create(User $user): bool
    {
        return $user->managesSite();
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return $user->managesSite();
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return $user->managesSite();
    }
}
