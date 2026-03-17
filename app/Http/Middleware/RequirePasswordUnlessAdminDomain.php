<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequirePasswordUnlessAdminDomain
{
    public function __construct(private RequirePassword $requirePassword) {}

    public function handle(Request $request, Closure $next): Response
    {
        $adminDomain = 'admin.'.(string) collect(config('tenancy.central_domains'))->first();

        if ($request->getHost() === $adminDomain) {
            return $next($request);
        }

        return $this->requirePassword->handle($request, $next);
    }
}
