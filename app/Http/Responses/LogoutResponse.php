<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as FortifyLogoutResponse;

final class LogoutResponse implements FortifyLogoutResponse
{
    private const array EXCLUDED_CENTRAL_DOMAINS = ['localhost', '127.0.0.1'];

    /**
     * Return the response for the logout request.
     *
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        $currentTenantDomain = tenancy()->tenant->domains()->first();

        if ($currentTenantDomain) {
            $url = sprintf(
                '%s://%s/%s',
                $request->getScheme(),
                $currentTenantDomain->domain,
                'login'
            );

            return redirect()->away($url);
        }

        $centralDomain = collect(config('tenancy.central_domains'))
            ->reject(fn (string $domain): bool => in_array($domain, self::EXCLUDED_CENTRAL_DOMAINS))
            ->first();

        $url = sprintf('%s://%s', $request->getScheme(), $centralDomain);

        return redirect()->away($url);
    }
}
