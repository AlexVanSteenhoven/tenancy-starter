<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUUIDAsPrimaryKey;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Billable;
use Ramsey\Uuid\UuidInterface;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant;

/**
 * @property string|UuidInterface $id
 * @property string $name
 * @property string|null $plan
 * @property array<string, mixed>|null $data
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Collection<int, Domain> $domains
 */
final class Workspace extends Tenant implements TenantWithDatabase
{
    use Billable;
    use HasDatabase;
    use HasDomains;
    use HasUUIDAsPrimaryKey;

    /**
     * @var string
     */
    protected $table = 'workspaces';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'plan',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    /**
     * @return BelongsTo<Plan, Workspace>
     */
    public function billingPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan', 'slug');
    }
}
