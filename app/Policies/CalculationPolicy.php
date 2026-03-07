<?php

namespace App\Policies;

use App\Models\Calculation;
use App\Models\User;

class CalculationPolicy
{
    public function view(User $user, Calculation $calculation): bool
    {
        return $user->isAdmin()
            || $calculation->user_id === $user->id
            || $calculation->isSharedWith($user);
    }

    public function update(User $user, Calculation $calculation): bool
    {
        if ($user->isAdmin() || $calculation->user_id === $user->id) {
            return true;
        }

        return $calculation->getSharePermission($user) === 'edit';
    }

    public function delete(User $user, Calculation $calculation): bool
    {
        return $user->isAdmin() || $calculation->user_id === $user->id;
    }

    public function share(User $user, Calculation $calculation): bool
    {
        return $user->isAdmin() || $calculation->user_id === $user->id;
    }
}
