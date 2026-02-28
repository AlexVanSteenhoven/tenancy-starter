<?php

declare(strict_types=1);

test('returns a successful response', function (): void {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('tenant host home request ignores stale auth session without tenancy context', function (): void {
    $guardSessionKey = app('auth')->guard()->getName();

    $response = $this
        ->withSession([
            $guardSessionKey => '019c99cd-20b9-7369-8a9c-2b7625cd1396',
        ])
        ->get('http://modus-digital.tenancy-starter.test/');

    $response->assertOk();
});
