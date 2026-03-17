<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Plans;

use App\Actions\Admin\StorePlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use Illuminate\Http\RedirectResponse;

final class StorePlanController extends Controller
{
    public function __invoke(StorePlanRequest $request, StorePlanAction $action): RedirectResponse
    {
        $action->handle($request);

        return back()->with('status', __('admin.plans.messages.created'));
    }
}
