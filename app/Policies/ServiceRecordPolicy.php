<?php

namespace App\Policies;

use App\Models\ServiceRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceRecord $serviceRecord): bool
    {
        return $user->id === $serviceRecord->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceRecord $serviceRecord): bool
    {
        return $user->id === $serviceRecord->user_id;
    }

    public function delete(User $user, ServiceRecord $serviceRecord): bool
    {
        return $user->id === $serviceRecord->user_id;
    }

    public function restore(User $user, ServiceRecord $serviceRecord): bool
    {
        return $user->id === $serviceRecord->user_id;
    }

    public function forceDelete(User $user, ServiceRecord $serviceRecord): bool
    {
        return $user->id === $serviceRecord->user_id;
    }
}
