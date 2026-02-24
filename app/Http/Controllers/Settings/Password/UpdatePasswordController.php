<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings\Password;

use App\Actions\Settings\PasswordUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;

final class UpdatePasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PasswordUpdateRequest $request, PasswordUpdateAction $action): RedirectResponse
    {
        $action->handle(
            request: $request
        );

        return back();
    }
}
