<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;

class LoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->intended(
            default: config(
                key: 'fortify.home'
            )
        );
    }
}
