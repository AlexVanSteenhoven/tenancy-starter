<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workspaces;

use App\Actions\Admin\CancelWorkspaceSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;

final class CancelWorkspaceSubscriptionController extends Controller
{
    public function __invoke(Workspace $workspace, CancelWorkspaceSubscriptionAction $action): RedirectResponse
    {
        $action->handle($workspace);

        return back()->with('status', __('admin.workspaces.messages.subscription_canceled'));
    }
}
