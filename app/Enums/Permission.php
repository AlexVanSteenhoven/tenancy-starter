<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case ManageWorkspace = 'manage:workspace';
    case ManageRoles = 'manage:roles';
    case InviteMembers = 'invite:members';
    case RemoveMembers = 'remove:members';
    case ViewMembers = 'view:members';

    /**
     * @return array<self>
     */
    public static function forRole(Role $role): array
    {
        return match ($role) {
            Role::Owner => self::cases(),
            Role::Admin => [
                self::InviteMembers,
                self::RemoveMembers,
                self::ViewMembers,
                self::ManageRoles,
            ],
            Role::Member => [
                self::ViewMembers,
            ],
        };
    }
}
