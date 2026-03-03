<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case Active = 'active';
    case Deleted = 'deleted';
    case Inactive = 'inactive';
    case Blocked = 'blocked';
    case Pending = 'pending';
    case Suspended = 'suspended';
}
