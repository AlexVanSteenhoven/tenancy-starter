<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workspaces;

use App\Actions\Admin\UpdateWorkspaceSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWorkspaceSubscriptionRequest;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;

final class UpdateWorkspaceSubscriptionController extends Controller
{
    public function __invoke(
        UpdateWorkspaceSubscriptionRequest $request,
        Workspace $workspace,
        UpdateWorkspaceSubscriptionAction $action
    ): RedirectResponse {
        $action->handle($request, $workspace);

        return back()->with('status', __('admin.workspaces.messages.subscription_updated'));
    }
}
