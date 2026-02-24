<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

final class TwoFactorResponse implements TwoFactorLoginResponseContract
{
    private const array EXCLUDED_CENTRAL_DOMAINS = ['localhost', '127.0.0.1'];

    /**
     * Return the response for the two factor login request.
     *
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        $tenantDomain = tenancy()->tenant->domains()->first()->domain;
        if ($tenantDomain) {
            $redirectUrl = sprintf(
                '%s://%s.%s',
                $request->getScheme(),
                $tenantDomain,
                config('fortify.home')
            );
        }

        $centralDomain = collect(config('tenancy.central_domains'))
            ->reject(fn (string $domain): bool => in_array($domain, self::EXCLUDED_CENTRAL_DOMAINS))
            ->first();

        $redirectUrl = sprintf('%s://%s', $request->getScheme(), $centralDomain);

        return redirect()->intended($redirectUrl);
    }
}
