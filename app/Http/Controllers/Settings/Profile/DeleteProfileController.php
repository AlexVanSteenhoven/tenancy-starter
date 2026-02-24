<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings\Profile;

use App\Actions\Settings\ProfileDeleteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use Illuminate\Http\RedirectResponse;

final class DeleteProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProfileDeleteRequest $request, ProfileDeleteAction $action): RedirectResponse
    {
        $action->handle(
            request: $request
        );

        return redirect('/');
    }
}
