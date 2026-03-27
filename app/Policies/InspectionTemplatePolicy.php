<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class InspectionTemplatePolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'inspection_template.manage';
    }
}
