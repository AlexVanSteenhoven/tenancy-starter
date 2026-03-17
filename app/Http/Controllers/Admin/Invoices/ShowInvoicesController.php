<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\Stripe\StripeAdminService;
use Inertia\Inertia;
use Inertia\Response;

final class ShowInvoicesController extends Controller
{
    public function __invoke(StripeAdminService $stripeAdminService): Response
    {
        $statusFilter = request()->string('status')->toString();
        $invoices = $stripeAdminService->listInvoices(
            limit: 100,
            status: $statusFilter !== '' ? $statusFilter : null,
        );

        $workspacesByStripeId = Workspace::query()
            ->whereNotNull('stripe_id')
            ->get(['id', 'name', 'stripe_id'])
            ->keyBy('stripe_id');

        return Inertia::render('admin/invoices/index', [
            'invoices' => collect($invoices->data)->map(function ($invoice) use ($workspacesByStripeId): array {
                $workspace = $workspacesByStripeId->get($invoice->customer);

                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'customer' => $invoice->customer,
                    'workspace_name' => $workspace?->name,
                    'amount_paid' => $invoice->amount_paid,
                    'amount_due' => $invoice->amount_due,
                    'currency' => $invoice->currency,
                    'status' => $invoice->status,
                    'hosted_invoice_url' => $invoice->hosted_invoice_url,
                    'invoice_pdf' => $invoice->invoice_pdf,
                    'created' => $invoice->created,
                ];
            })->values(),
        ]);
    }
}
