<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Http\Requests\Onboarding\StoreSetupAccountRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class StoreSetupAccountAction
{
    /**
     * Execute the action.
     */
    public function handle(StoreSetupAccountRequest $request): void
    {
        $user = DB::transaction(function () use ($request): User {
            return User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ]);
        });

        $user->sendEmailVerificationNotification();

        Auth::login($user);
    }
}
