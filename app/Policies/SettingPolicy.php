<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    /**
     * Global site settings are admin-only.
     */
    public function viewAny(User $user): bool
    {
        return $user->managesSite();
    }

    public function update(User $user, ?Setting $setting = null): bool
    {
        return $user->managesSite();
    }
}
