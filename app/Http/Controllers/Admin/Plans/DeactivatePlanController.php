<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Plans;

use App\Actions\Admin\DeactivatePlanAction;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;

final class DeactivatePlanController extends Controller
{
    public function __invoke(Plan $plan, DeactivatePlanAction $action): RedirectResponse
    {
        $action->handle($plan);

        return back()->with('status', __('admin.plans.messages.deactivated'));
    }
}
