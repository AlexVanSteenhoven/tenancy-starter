<?php

declare(strict_types=1);

use App\Models\Workspace;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function (): void {
    $this->withoutVite();
    Event::fake([TenantCreated::class]);
});

test('workspace creation redirects to billing and stores workspace in session', function (): void {
    Notification::fake();

    $response = $this->post('/onboarding/create-workspace', [
        'workspace' => 'Acme Workspace',
        'email' => 'owner@acme.com',
    ]);

    $response
        ->assertRedirect(route('onboarding.billing'))
        ->assertSessionHas('onboarding_workspace_id');

    $workspace = Workspace::query()
        ->where('name', 'Acme Workspace')
        ->firstOrFail();

    $this->assertSame('owner@acme.com', $workspace->onboarding_email);
    Notification::assertNothingSent();
});
