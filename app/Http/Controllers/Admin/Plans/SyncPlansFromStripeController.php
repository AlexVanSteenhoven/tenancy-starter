<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Plans;

use App\Actions\Admin\SyncPlansFromStripeAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class SyncPlansFromStripeController extends Controller
{
    public function __invoke(SyncPlansFromStripeAction $action): RedirectResponse
    {
        $result = $action->handle();

        return back()->with('status', __('admin.plans.messages.synced', $result));
    }
}
