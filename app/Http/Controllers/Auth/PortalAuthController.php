<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalAuthController extends Controller
{
    public function showAdminLogin()
    {
        $user = Auth::guard('admin')->user();
        if ($user && $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.portal-login', [
            'portal'      => 'admin',
            'badge'       => 'PORTAL ADMIN GOR',
            'title'       => 'Login Panel Admin',
            'subtitle'    => 'Sesi ini berjalan mandiri — tidak mengganggu tab Kasir maupun User yang sedang aktif.',
            'formAction'  => route('admin.login.submit'),
            'placeholder' => 'admin@gorfutsal.test',
        ]);
    }

    public function loginAdmin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])->onlyInput('email');
        }

        if ($user->role !== 'admin') {
            return back()->withErrors([
                'email' => 'Akun ini bukan role Admin. Gunakan /kasir/login untuk Kasir atau /login untuk User.',
            ])->onlyInput('email');
        }

        Auth::guard('admin')->login($user, $request->boolean('remember'));
        $request->session()->migrate(false);

        return redirect()->route('admin.dashboard');
    }

    public function logoutAdmin(Request $request)
    {
        Auth::guard('admin')->logout();

        return redirect()->route('admin.login')
            ->with('status', 'Sesi Admin telah keluar. Tab Kasir & User tetap aktif.');
    }

    public function showKasirLogin()
    {
        $user = Auth::guard('kasir')->user();
        if ($user && $user->role === 'kasir') {
            return redirect()->route('kasir.dashboard');
        }

        return view('auth.portal-login', [
            'portal'      => 'kasir',
            'badge'       => 'PORTAL KASIR GOR',
            'title'       => 'Login Meja Kasir',
            'subtitle'    => 'Sesi ini berjalan mandiri — tidak mengganggu tab Admin maupun User yang sedang aktif.',
            'formAction'  => route('kasir.login.submit'),
            'placeholder' => 'kasir@gorfutsal.test',
        ]);
    }

    public function loginKasir(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])->onlyInput('email');
        }

        if ($user->role !== 'kasir') {
            return back()->withErrors([
                'email' => 'Akun ini bukan role Kasir. Gunakan /admin/login untuk Admin atau /login untuk User.',
            ])->onlyInput('email');
        }

        Auth::guard('kasir')->login($user, $request->boolean('remember'));
        $request->session()->migrate(false);

        return redirect()->route('kasir.dashboard');
    }

    public function logoutKasir(Request $request)
    {
        Auth::guard('kasir')->logout();

        return redirect()->route('kasir.login')
            ->with('status', 'Sesi Kasir telah keluar. Tab Admin & User tetap aktif.');
    }

    public function showUserLogin()
    {
        return view('auth.portal-login', [
            'portal'      => 'user',
            'badge'       => 'PORTAL USER / PENYEWA',
            'title'       => 'Login Akun Penyewa',
            'subtitle'    => 'Sesi User berjalan terpisah dari sesi Admin & Kasir di tab lain.',
            'formAction'  => route('login.submit'),
            'placeholder' => 'user@gorfutsal.test',
        ]);
    }

    public function loginUser(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])->onlyInput('email');
        }

        if ($user->role === 'admin') {
            Auth::guard('admin')->login($user, $request->boolean('remember'));
            $request->session()->migrate(false);
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'kasir') {
            Auth::guard('kasir')->login($user, $request->boolean('remember'));
            $request->session()->migrate(false);
            return redirect()->route('kasir.dashboard');
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->migrate(false);

        return redirect()->route('home');
    }

    public function logoutUser(Request $request)
    {
        Auth::guard('web')->logout();

        return redirect()->route('home')
            ->with('success', 'Anda telah keluar dari akun User.');
    }
}
