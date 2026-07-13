<?php

namespace App\Policies;

use App\Models\Governorate;
use App\Models\User;

class GovernoratePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Governorate $governorate): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Governorate $governorate): bool
    {
        return true;
    }

    public function delete(User $user, Governorate $governorate): bool
    {
        return true;
    }
}