<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowLoginController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('admin/auth/login', [
            'status' => $request->session()->get('status'),
        ]);
    }
}
