<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Enums\Permission;
use App\Enums\Role;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

final class ShowUsersController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $cacheKey = sprintf('tenant.%s.users.index', tenancy()->tenant?->id ?? $request->getHost());

        $users = Cache::remember(
            key: $cacheKey,
            ttl: now()->addDays(1),
            callback: function (): array {
                $activeUsers = User::query()
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
                        'type' => 'user',
                    ])
                    ->all();

                $pendingInvitations = Invitation::query()
                    ->select(['id', 'email', 'role', 'created_at'])
                    ->whereNull('accepted_at')
                    ->where('expires_at', '>', now())
                    ->latest()
                    ->get()
                    ->map(fn (Invitation $invitation): array => [
                        'id' => $invitation->id,
                        'name' => null,
                        'email' => $invitation->email,
                        'status' => 'pending',
                        'role' => $invitation->role,
                        'created_at' => $invitation->created_at?->toDateTimeString(),
                        'type' => 'invitation',
                    ])
                    ->all();

                return array_merge($activeUsers, $pendingInvitations);
            },
        );

        return Inertia::render(
            component: 'users/show-users',
            props: [
                'users' => $users,
                'permissions' => [
                    'canInviteUsers' => $request->user()?->hasPermissionTo(permission: Permission::InviteUsers) ?? false,
                    'canUpdateUsers' => $request->user()?->hasPermissionTo(permission: Permission::UpdateUsers) ?? false,
                    'canDeleteUsers' => $request->user()?->hasPermissionTo(permission: Permission::DeleteUsers) ?? false,
                    'canViewUsers' => $request->user()?->hasPermissionTo(permission: Permission::ViewUsers) ?? false,
                ],
                'filters' => [
                    'status' => array_map(
                        static fn (Status $status): string => $status->value,
                        Status::cases(),
                    ),
                    'role' => array_map(
                        static fn (Role $role): string => $role->value,
                        Role::cases(),
                    ),
                ],
            ],
        );
    }
}
