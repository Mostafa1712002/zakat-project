<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect sales reps to their dashboard
        if (Auth::user()->isSalesRep() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('sales-rep.dashboard');
        }

        // Redirect employees to their dashboard
        if (Auth::user()->isEmployee() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('employee.dashboard');
        }

        // Redirect others to main dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Clear intended URL to prevent redirect loop
        $request->session()->forget('url.intended');

        return redirect()->route('login');
    }
}
