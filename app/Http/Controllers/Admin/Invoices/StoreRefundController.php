<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Invoices;

use App\Actions\Admin\StoreRefundAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRefundRequest;
use Illuminate\Http\RedirectResponse;

final class StoreRefundController extends Controller
{
    public function __invoke(
        StoreRefundRequest $request,
        string $invoice,
        StoreRefundAction $action
    ): RedirectResponse {
        $action->handle($request, $invoice);

        return back()->with('status', __('admin.invoices.messages.refund_created'));
    }
}
