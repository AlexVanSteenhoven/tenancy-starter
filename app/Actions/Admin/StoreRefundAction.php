<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Http\Requests\Admin\StoreRefundRequest;
use App\Services\Stripe\StripeAdminService;
use Illuminate\Validation\ValidationException;

final readonly class StoreRefundAction
{
    public function __construct(private StripeAdminService $stripeAdminService) {}

    public function handle(StoreRefundRequest $request, string $invoiceId): void
    {
        $invoice = $this->stripeAdminService->retrieveInvoice($invoiceId);

        if (! is_string($invoice->charge) || $invoice->charge === '') {
            throw ValidationException::withMessages([
                'invoice' => __('admin.invoices.validation.missing_charge'),
            ]);
        }

        $this->stripeAdminService->createRefund(
            chargeId: $invoice->charge,
            amountInCents: $request->integer('amount') ?: null,
            reason: $request->string('reason')->toString() !== '' ? $request->string('reason')->toString() : null,
        );
    }
}
