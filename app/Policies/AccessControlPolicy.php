<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class AccessControlPolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'access.manage';
    }
}
