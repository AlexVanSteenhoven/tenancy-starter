<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

final class ShowUsersController extends Controller
{
    public function __invoke(): Response
    {
        $users = User::query()
            ->select(columns: ['id', 'name', 'email', 'status', 'created_at'])
            ->with(relations: ['roles' => function ($query): void {
                $query->select('roles.id', 'name')->orderBy('roles.id');
            }])
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'role' => $user->roles->first()?->name,
                'created_at' => $user->created_at?->toDateTimeString(),
            ]);

        $pendingInvitations = Invitation::query()
            ->select(['id', 'email', 'role', 'invited_by_id', 'created_at'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('invitedBy:id,name')
            ->latest()
            ->get()
            ->map(fn (Invitation $invitation): array => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'invited_by' => $invitation->invitedBy?->name,
                'invited_at' => $invitation->created_at?->toDateTimeString(),
            ]);

        return Inertia::render(
            component: 'users/show-users',
            props: [
                'users' => $users,
                'pendingInvitations' => $pendingInvitations,
            ],
        );
    }
}
