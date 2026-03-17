<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('admin.auth.validation.invalid_credentials'),
            ]);
        }

        $request->session()->regenerate();

        if (! auth()->user()?->hasPermissionTo(Permission::AccessAdminPanel->value)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('admin.auth.validation.not_allowed'),
            ]);
        }

        return to_route('admin.dashboard');
    }
}
