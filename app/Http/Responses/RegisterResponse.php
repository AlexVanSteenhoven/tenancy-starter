<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as FortifyRegisterResponse;

final class RegisterResponse implements FortifyRegisterResponse
{
    /**
     * Return the response for the register request.
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

            return redirect()->away($redirectUrl);
        }

        return redirect(config('fortify.home'));
    }
}
