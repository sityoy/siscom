<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse|View
    {
        $user = auth()->user();

        if ($user?->hasAnyRole(['super_admin', 'admin'])) {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->hasRole('client')) {
            return redirect()->route('client.dashboard');
        }

        return view('dashboard');
    }
}
