<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->isSchoolAdmin();
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin()) return true;
        if ($actor->isSchoolAdmin()) return $target->school_id === $actor->school_id && !$target->isSuperAdmin();
        return $actor->id === $target->id; // fallback
    }

    public function create(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->isSchoolAdmin();
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin()) return true;
        if ($actor->isSchoolAdmin()) {
            return $target->school_id === $actor->school_id && !$target->isSuperAdmin() && !$target->isSchoolAdmin();
        }
        return false;
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false; // cannot delete self
        if ($actor->isSuperAdmin()) return true;
        if ($actor->isSchoolAdmin()) {
            return $target->school_id === $actor->school_id && !$target->isSuperAdmin() && !$target->isSchoolAdmin();
        }
        return false;
    }
}
