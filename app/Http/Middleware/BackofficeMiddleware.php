<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BackofficeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*')) {
            $user = Auth::guard('admin')->user();
            if (! $user || $user->role !== 'admin') {
                return redirect()->route('admin.login');
            }
            Auth::shouldUse('admin');
            return $next($request);
        }

        if ($request->is('kasir*')) {
            $user = Auth::guard('kasir')->user();
            if (! $user || $user->role !== 'kasir') {
                return redirect()->route('kasir.login');
            }
            Auth::shouldUse('kasir');
            return $next($request);
        }

        $user = Auth::guard('admin')->user() ?? Auth::guard('kasir')->user();
        if (! $user || ! in_array($user->role, ['admin', 'kasir'], true)) {
            abort(403, 'Akses terbatas hanya untuk Admin dan Kasir GOR.');
        }

        return $next($request);
    }
}
