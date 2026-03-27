<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesByPermission;

class AssetPolicy
{
    use AuthorizesByPermission;

    protected function permissionCode(): string
    {
        return 'asset.manage';
    }
}
