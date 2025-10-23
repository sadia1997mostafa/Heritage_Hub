<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Models\Admin;

class EventPolicy
{
    public function viewAny($user)
    {
        if (!$user) return false;
        if ($user instanceof Admin) return true;
        return property_exists($user, 'is_admin') && $user->is_admin;
    }

    public function update($user, Event $event)
    {
        return $user && $event->user_id === $user->id;
    }

    public function delete($user, Event $event)
    {
        return $user && $event->user_id === $user->id;
    }

    public function approve($user, Event $event)
    {
        if (!$user) return false;
        if ($user instanceof Admin) return true;
        return property_exists($user, 'is_admin') && $user->is_admin;
    }
}
