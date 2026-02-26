<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Http\Requests\Onboarding\StoreOnboardingRequest;
use App\Models\Workspace;
use Illuminate\Support\Str;

final readonly class StoreOnboardingAction
{
    /**
     * Execute the action.
     */
    public function handle(StoreOnboardingRequest $request): void
    {
        $name = mb_trim($request->input('workspace'));
        $email = mb_trim($request->input('email'));
        $subdomain = Str::slug($name);

        $workspace = Workspace::create([
            'name' => $name,
            'onboarding_email' => $email,
        ]);

        $workspace->domains()->create([
            'domain' => $subdomain,
        ]);
    }
}
