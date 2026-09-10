<?php

namespace App\Policies;

use App\Models\FuelRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FuelRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FuelRecord $fuelRecord): bool
    {
        return $user->id === $fuelRecord->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FuelRecord $fuelRecord): bool
    {
        return $user->id === $fuelRecord->user_id;
    }

    public function delete(User $user, FuelRecord $fuelRecord): bool
    {
        return $user->id === $fuelRecord->user_id;
    }

    public function restore(User $user, FuelRecord $fuelRecord): bool
    {
        return $user->id === $fuelRecord->user_id;
    }

    public function forceDelete(User $user, FuelRecord $fuelRecord): bool
    {
        return $user->id === $fuelRecord->user_id;
    }
}
