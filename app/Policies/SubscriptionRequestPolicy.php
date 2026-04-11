<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubscriptionRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubscriptionRequest');
    }

    public function view(AuthUser $authUser, SubscriptionRequest $subscriptionRequest): bool
    {
        return $authUser->can('View:SubscriptionRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubscriptionRequest');
    }

    public function update(AuthUser $authUser, SubscriptionRequest $subscriptionRequest): bool
    {
        return $authUser->can('Update:SubscriptionRequest');
    }

    public function delete(AuthUser $authUser, SubscriptionRequest $subscriptionRequest): bool
    {
        return $authUser->can('Delete:SubscriptionRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SubscriptionRequest');
    }

    public function restore(AuthUser $authUser, SubscriptionRequest $subscriptionRequest): bool
    {
        return $authUser->can('Restore:SubscriptionRequest');
    }

    public function forceDelete(AuthUser $authUser, SubscriptionRequest $subscriptionRequest): bool
    {
        return $authUser->can('ForceDelete:SubscriptionRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubscriptionRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubscriptionRequest');
    }

    public function replicate(AuthUser $authUser, SubscriptionRequest $subscriptionRequest): bool
    {
        return $authUser->can('Replicate:SubscriptionRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubscriptionRequest');
    }

}