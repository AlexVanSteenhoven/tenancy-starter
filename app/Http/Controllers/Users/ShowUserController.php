<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class ShowUserController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('users/show-user');
    }
}
