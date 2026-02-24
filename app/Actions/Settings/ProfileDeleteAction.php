<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Http\Requests\Settings\ProfileDeleteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class ProfileDeleteAction
{
    /**
     * Execute the action.
     */
    public function handle(ProfileDeleteRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $user = $request->user();

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        });
    }
}
