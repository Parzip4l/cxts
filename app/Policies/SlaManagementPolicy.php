<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class SlaManagementPolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'sla.manage';
    }
}
