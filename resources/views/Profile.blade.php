@extends('layouts.app')

@section('title', 'Profil Akun — GOR Futsal Nusantara')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div>
        <span class="text-xs uppercase tracking-wider text-accent font-bold">PENGATURAN AKUN</span>
        <h1 class="text-3xl font-bold text-ink">Profil Saya</h1>
    </div>

    <div class="wf-box rounded-2xl p-6 space-y-4">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4 text-xs">
            @csrf
            @method('PATCH')

            <div>
                <label class="block font-bold mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-ink/25 text-sm">
            </div>

            <div>
                <label class="block font-bold mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border-ink/25 text-sm">
            </div>

            <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent text-paper font-bold">
                Simpan Perubahan Profil
            </button>
        </form>
    </div>

    <div class="wf-box rounded-2xl p-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-ink">Keluar dari Sesi Akun</p>
            <p class="text-xs text-ink/70">Akhiri sesi login Anda di perangkat ini.</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-700 text-white text-xs font-bold">
                Logout
            </button>
        </form>
    </div>
</div>
@endsection
