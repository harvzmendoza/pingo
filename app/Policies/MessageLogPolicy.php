<?php

namespace App\Policies;

use App\Models\MessageLog;
use App\Models\User;

class MessageLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MessageLog $messageLog): bool
    {
        return $messageLog->message !== null && $user->id === $messageLog->message->user_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, MessageLog $messageLog): bool
    {
        return false;
    }

    public function delete(User $user, MessageLog $messageLog): bool
    {
        return false;
    }

    public function restore(User $user, MessageLog $messageLog): bool
    {
        return false;
    }

    public function forceDelete(User $user, MessageLog $messageLog): bool
    {
        return false;
    }
}
