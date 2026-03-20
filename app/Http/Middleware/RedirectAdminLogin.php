<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RedirectAdminLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminDomain = 'admin.'.(string) collect(config('tenancy.central_domains'))->first();

        if ($request->getHost() === $adminDomain && $request->getPathInfo() === '/login') {
            return redirect()->to($request->getSchemeAndHttpHost().'/_/login');
        }

        return $next($request);
    }
}
