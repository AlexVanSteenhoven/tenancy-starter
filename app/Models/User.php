<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUUIDAsPrimaryKey;
use App\Enums\Status;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Ramsey\Uuid\UuidInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string|UuidInterface $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $two_factor_secret
 * @property string[]|null $two_factor_recovery_codes
 * @property CarbonInterface|null $two_factor_confirmed_at
 * @property CarbonInterface|null $email_verified_at
 * @property Status $status
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Collection<int, Role> $roles
 * @property-read Collection<int, Permission> $permissions
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasRoles;
    use HasUUIDAsPrimaryKey;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $request = app(Request::class);

        $this->notify(new ResetPasswordNotification(
            token: (string) $token,
            tenantHost: $request->getHost(),
            tenantScheme: $request->getScheme(),
        ));
    }

    public function sendEmailVerificationNotification(): void
    {
        $request = app(Request::class);

        $this->notify(new VerifyEmailNotification(
            tenantHost: $request->getHost(),
            tenantScheme: $request->getScheme(),
        ));
    }
}
