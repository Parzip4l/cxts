<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class OrganizationPolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'organization.manage';
    }
}
