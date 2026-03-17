<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Workspace;

final readonly class CancelWorkspaceSubscriptionAction
{
    public function handle(Workspace $workspace): void
    {
        $workspace->subscription('default')?->cancel();
    }
}
