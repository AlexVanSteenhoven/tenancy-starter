<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Plans;

use App\Actions\Admin\UpdatePlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;

final class UpdatePlanController extends Controller
{
    public function __invoke(UpdatePlanRequest $request, Plan $plan, UpdatePlanAction $action): RedirectResponse
    {
        $action->handle($request, $plan);

        return back()->with('status', __('admin.plans.messages.updated'));
    }
}
