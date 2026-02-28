<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUUIDAsPrimaryKey;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $email
 * @property string $role
 * @property string $token
 * @property string $invited_by_id
 * @property CarbonInterface|null $accepted_at
 * @property CarbonInterface $expires_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
final class Invitation extends Model
{
    use HasUUIDAsPrimaryKey;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'role',
        'token',
        'invited_by_id',
        'accepted_at',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}
