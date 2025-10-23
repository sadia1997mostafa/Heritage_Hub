<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vlog;
use App\Models\Admin;

class VlogPolicy
{
    public function viewAny($user)
    {
        // Allow Admin model instances (admin guard) or users with is_admin flag
        if (!$user) return false;
        if ($user instanceof Admin) return true;
        return property_exists($user, 'is_admin') && $user->is_admin;
    }

    public function update($user, Vlog $vlog)
    {
        return $user && $vlog->user_id === $user->id;
    }

    public function delete($user, Vlog $vlog)
    {
        return $user && $vlog->user_id === $user->id;
    }

    public function approve($user, Vlog $vlog)
    {
        if (!$user) return false;
        if ($user instanceof Admin) return true;
        return property_exists($user, 'is_admin') && $user->is_admin;
    }
}
