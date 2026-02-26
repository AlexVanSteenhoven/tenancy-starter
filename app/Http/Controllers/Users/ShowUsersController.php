<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

final class ShowUsersController extends Controller
{
    public function __invoke(): Response
    {
        $users = User::all();

        return Inertia::render('users/show-users', [
            'users' => $users,
        ]);
    }
}
