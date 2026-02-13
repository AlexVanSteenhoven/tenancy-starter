<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements LogoutResponseContract
{
    private const string HTTP_SCHEME = 'http';
    private const string HTTPS_SCHEME = 'https';
    
    public function toResponse($request): RedirectResponse
    {
        $scheme = $request->isSecure() 
                ? self::HTTPS_SCHEME
                : self::HTTP_SCHEME;
                
        $domain = tenancy()->tenant?->domains->first();

        if ($domain) {
            $url = "{$scheme}://{$domain->domain}/login";

            return redirect()->away(path: $url);
        }

        $centralDomain = config(key: 'tenancy.central_domains.0');

        return redirect()->away(path: "{$scheme}://{$centralDomain}");
    }
}
