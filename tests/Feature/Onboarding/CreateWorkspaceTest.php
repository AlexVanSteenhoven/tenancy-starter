<?php

declare(strict_types=1);

use App\Models\Workspace;
use App\Notifications\WorkspaceReadyMail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Stancl\Tenancy\Events\DatabaseSeeded;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function (): void {
    $this->withoutVite();
    Event::fake([TenantCreated::class]);
});

test('workspace ready mail is sent only after tenant database is seeded', function (): void {
    Notification::fake();

    $response = $this->post('/onboarding/create-workspace', [
        'workspace' => 'Acme Workspace',
        'email' => 'owner@acme.com',
    ]);

    $response
        ->assertRedirect(route('onboarding.create-workspace'))
        ->assertSessionHas('status', __('onboarding.messages.check_email'));

    $workspace = Workspace::query()
        ->where('name', 'Acme Workspace')
        ->firstOrFail();

    $this->assertSame('owner@acme.com', $workspace->onboarding_email);

    Notification::assertNothingSent();

    event(new DatabaseSeeded($workspace));

    Notification::assertSentOnDemand(
        WorkspaceReadyMail::class,
        function (WorkspaceReadyMail $notification, array $channels, object $notifiable): bool {
            return $notification->workspaceName === 'Acme Workspace'
                && $notification->workspaceDomain === 'acme-workspace'
                && $notification->email === 'owner@acme.com'
                && $channels === ['mail']
                && $notifiable->routeNotificationFor('mail') === 'owner@acme.com';
        },
    );

    $workspace->refresh();

    expect($workspace->getRawOriginal('data'))->toBeNull();
});
