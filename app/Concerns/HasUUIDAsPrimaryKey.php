<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait HasUUIDAsPrimaryKey
{
    use HasUuids;

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
