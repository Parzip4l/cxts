<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class TaxonomyPolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'taxonomy.manage';
    }
}
