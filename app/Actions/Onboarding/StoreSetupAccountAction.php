<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Enums\Role;
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
            $_user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ]);

            $_user->assignRole(Role::Owner);

            return $_user;
        });

        $user->sendEmailVerificationNotification();

        Auth::login($user);
    }
}
