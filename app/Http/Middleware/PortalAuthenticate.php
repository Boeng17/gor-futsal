<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalAuthenticate
{
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        if ($guard === 'admin') {
            $user = Auth::guard('admin')->user();
            if (! $user || $user->role !== 'admin') {
                if ($user) {
                    Auth::guard('admin')->logout();
                }
                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'Silakan login menggunakan akun Admin GOR untuk mengakses portal ini.']);
            }
            Auth::shouldUse('admin');
            return $next($request);
        }

        if ($guard === 'kasir') {
            $user = Auth::guard('kasir')->user();
            if (! $user || $user->role !== 'kasir') {
                if ($user) {
                    Auth::guard('kasir')->logout();
                }
                return redirect()->route('kasir.login')
                    ->withErrors(['email' => 'Silakan login menggunakan akun Kasir GOR untuk mengakses portal ini.']);
            }
            Auth::shouldUse('kasir');
            return $next($request);
        }

        $user = Auth::guard('web')->user();
        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Silakan login terlebih dahulu sebagai User.']);
        }

        Auth::shouldUse('web');
        return $next($request);
    }
}
