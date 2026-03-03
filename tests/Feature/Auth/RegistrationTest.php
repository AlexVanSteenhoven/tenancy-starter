<?php

declare(strict_types=1);

beforeEach(function (): void {
    bootstrapTenantAwareFeatureTest($this);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    expect(parse_url((string) $response->headers->get('Location'), PHP_URL_PATH))->toBe('/dashboard');
});
