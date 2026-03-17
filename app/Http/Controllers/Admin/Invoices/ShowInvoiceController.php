<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\Stripe\StripeAdminService;
use Inertia\Inertia;
use Inertia\Response;

final class ShowInvoiceController extends Controller
{
    public function __invoke(string $invoice, StripeAdminService $stripeAdminService): Response
    {
        $stripeInvoice = $stripeAdminService->retrieveInvoice($invoice);
        $workspace = Workspace::query()
            ->where('stripe_id', $stripeInvoice->customer)
            ->first();

        return Inertia::render('admin/invoices/show', [
            'invoice' => [
                'id' => $stripeInvoice->id,
                'number' => $stripeInvoice->number,
                'customer' => $stripeInvoice->customer,
                'workspace_name' => $workspace?->name,
                'amount_paid' => $stripeInvoice->amount_paid,
                'amount_due' => $stripeInvoice->amount_due,
                'subtotal' => $stripeInvoice->subtotal,
                'total' => $stripeInvoice->total,
                'currency' => $stripeInvoice->currency,
                'status' => $stripeInvoice->status,
                'charge' => $stripeInvoice->charge,
                'hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
                'invoice_pdf' => $stripeInvoice->invoice_pdf,
                'created' => $stripeInvoice->created,
            ],
        ]);
    }
}
