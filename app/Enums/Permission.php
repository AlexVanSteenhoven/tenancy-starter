<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case UpdateWorkspace = 'update:workspace';
    case DeleteWorkspace = 'delete:workspace';
    case InviteUsers = 'invite:users';
    case ViewUsers = 'view:users';
    case UpdateUsers = 'update:users';
    case DeleteUsers = 'delete:users';

    /**
     * @return array<self>
     */
    public static function forRole(Role $role): array
    {
        return match ($role) {
            Role::Owner => self::cases(),
            Role::Admin => [
                self::InviteUsers,
                self::ViewUsers,
                self::UpdateUsers,
                self::DeleteUsers,
            ],
            Role::Member => [
                self::ViewUsers,
            ],
        };
    }
}
