<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUUIDAsPrimaryKey;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant;

final class Workspace extends Tenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;
    use HasUUIDAsPrimaryKey;

    /**
     * @var string
     */
    protected $table = 'workspaces';

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'plan',
        ];
    }
}
