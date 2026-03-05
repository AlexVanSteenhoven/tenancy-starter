<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

final class ShowUserController extends Controller
{
    public function __invoke(string $user): Response
    {
        $targetUser = User::query()
            ->select(columns: ['id', 'name', 'email', 'status', 'created_at'])
            ->with(relations: ['roles' => function ($query): void {
                $query->select('roles.id', 'name');
            }])
            ->findOrFail($user);

        return Inertia::render('users/show-user', [
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'status' => $targetUser->status,
                'role' => $targetUser->roles->first()?->name,
                'created_at' => $targetUser->created_at?->toDateTimeString(),
            ],
        ]);
    }
}
