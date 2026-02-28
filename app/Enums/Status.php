<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
    case Blocked = 'blocked';
    case Suspended = 'suspended';
    case Deleted = 'deleted';
    case Archived = 'archived';
    case Verified = 'verified';
    case Unverified = 'unverified';
}
