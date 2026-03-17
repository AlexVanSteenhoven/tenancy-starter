<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property int $price_monthly
 * @property string|null $stripe_product_id
 * @property string|null $stripe_price_id
 * @property array<int, string>|null $features
 * @property bool $is_active
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
final class Plan extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_monthly',
        'stripe_product_id',
        'stripe_price_id',
        'features',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Workspace, Plan>
     */
    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'plan', 'slug');
    }
}
