<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Support\Facades\DB;

final readonly class PasswordUpdateAction
{
    /**
     * Execute the action.
     */
    public function handle(PasswordUpdateRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $request->user()->update([
                'password' => $request->password,
            ]);
        });
    }
}
