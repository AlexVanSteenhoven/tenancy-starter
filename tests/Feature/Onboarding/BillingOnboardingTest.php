<?php

declare(strict_types=1);

use App\Contracts\Onboarding\SubscribeWorkspaceToPlan;
use App\Enums\Plan as WorkspacePlan;
use App\Models\Plan;
use App\Models\Workspace;
use App\Notifications\WorkspaceReadyMail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function (): void {
    $this->withoutVite();
    Event::fake([TenantCreated::class]);
});

test('billing page redirects when onboarding workspace session is missing', function (): void {
    $response = $this->get(route('onboarding.billing'));

    $response->assertRedirect(route('onboarding.create-workspace'));
});

test('free plan billing updates workspace and sends setup email', function (): void {
    Notification::fake();
    seedBillingPlans();

    $workspace = Workspace::query()->create([
        'name' => 'Acme Workspace',
        'onboarding_email' => 'owner@acme.com',
    ]);

    $workspace->domains()->create([
        'domain' => 'acme-workspace',
    ]);

    $response = $this->withSession([
        'onboarding_workspace_id' => (string) $workspace->id,
    ])->post(route('onboarding.billing.store'), [
        'plan' => WorkspacePlan::Free->value,
        'seats' => 1,
    ]);

    $response
        ->assertRedirect(route('onboarding.create-workspace'))
        ->assertSessionHas('status', __('onboarding.messages.check_email'));

    $workspace->refresh();

    expect($workspace->plan)->toBe(WorkspacePlan::Free->value)
        ->and($workspace->onboarding_email)->toBeNull();

    Notification::assertSentOnDemand(
        WorkspaceReadyMail::class,
        fn (WorkspaceReadyMail $notification): bool => $notification->workspaceName === 'Acme Workspace'
            && $notification->workspaceDomain === 'acme-workspace'
            && $notification->email === 'owner@acme.com',
    );
});

test('paid plan billing delegates subscription creation', function (): void {
    Notification::fake();
    seedBillingPlans();

    $workspace = Workspace::query()->create([
        'name' => 'Acme Workspace',
        'onboarding_email' => 'owner@acme.com',
    ]);

    $workspace->domains()->create([
        'domain' => 'acme-workspace',
    ]);

    $mock = $this->mock(SubscribeWorkspaceToPlan::class);
    $mock->shouldReceive('handle')
        ->once()
        ->withArgs(function (Workspace $givenWorkspace, string $priceId, string $paymentMethod, int $seats) use ($workspace): bool {
            return $givenWorkspace->is($workspace)
                && $priceId === 'price_test_pro'
                && $paymentMethod === 'pm_card_visa'
                && $seats === 5;
        });

    $response = $this->withSession([
        'onboarding_workspace_id' => (string) $workspace->id,
    ])->post(route('onboarding.billing.store'), [
        'plan' => WorkspacePlan::Pro->value,
        'seats' => 5,
        'payment_method' => 'pm_card_visa',
    ]);

    $response->assertRedirect(route('onboarding.create-workspace'));

    $workspace->refresh();
    expect($workspace->plan)->toBe(WorkspacePlan::Pro->value);
});

function seedBillingPlans(): void
{
    Plan::query()->create([
        'slug' => WorkspacePlan::Free->value,
        'name' => 'Free',
        'price_monthly' => 0,
        'stripe_price_id' => null,
        'features' => [],
        'is_active' => true,
    ]);

    Plan::query()->create([
        'slug' => WorkspacePlan::Pro->value,
        'name' => 'Pro',
        'price_monthly' => 2900,
        'stripe_price_id' => 'price_test_pro',
        'features' => [],
        'is_active' => true,
    ]);
}
