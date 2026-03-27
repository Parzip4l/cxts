<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class WorkforcePolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'workforce.manage';
    }
}
