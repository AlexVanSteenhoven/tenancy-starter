<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Support\Facades\DB;

final class ProfileUpdateAction
{
    /**
     * Update the authenticated user's profile.
     */
    public function handle(ProfileUpdateRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();
        });
    }
}
