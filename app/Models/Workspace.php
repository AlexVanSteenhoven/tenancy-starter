<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUUIDAsPrimaryKey;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Uuid\UuidInterface;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant;

/**
 * @property string|UuidInterface $id
 * @property string $name
 * @property string $plan
 * @property array<string, mixed>|null $data
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Collection<int, Domain> $domains
 */
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
