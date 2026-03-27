<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AuthorizesByPermission
{
    public function viewAny(User $user): bool
    {
        return $this->allows($user);
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->allows($user);
    }

    public function create(User $user): bool
    {
        return $this->allows($user);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->allows($user);
    }

    public function delete(User $user, mixed $model): bool
    {
        return $this->allows($user);
    }

    protected function allows(User $user): bool
    {
        return $user->hasPermission($this->permissionCode());
    }

    abstract protected function permissionCode(): string;
}
