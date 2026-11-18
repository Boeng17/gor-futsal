<x-guest-layout>
    <x-slot name="title">{{ $title }} — GOR Futsal Nusantara</x-slot>
    <div class="space-y-5">
        <div class="border-b border-ink/15 pb-4">
            <span class="inline-block px-2.5 py-1 rounded bg-accent text-paper text-[10px] font-bold uppercase tracking-wider mb-2">
                {{ $badge }}
            </span>
            <h1 class="text-2xl font-bold text-ink">{{ $title }}</h1>
            <p class="text-xs text-ink/75 mt-1 leading-relaxed">{{ $subtitle }}</p>
        </div>

        @if (session('status'))
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-700/30 text-emerald-950 text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-700/30 text-rose-950 text-xs">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $formAction }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="email" class="block font-bold text-ink mb-1">Email Akun</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="{{ $placeholder }}"
                       class="w-full rounded-xl border-ink/25 bg-white text-sm focus:border-accent focus:ring-accent">
            </div>

            <div>
                <label for="password" class="block font-bold text-ink mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full rounded-xl border-ink/25 bg-white text-sm focus:border-accent focus:ring-accent">
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-ink/80 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-ink/30 text-accent focus:ring-accent">
                    <span>Ingat sesi ini</span>
                </label>
            </div>

            <button type="submit"
                    class="wf-btn w-full py-3 rounded-xl bg-accent hover:bg-accentDark text-paper font-bold text-xs uppercase tracking-wider">
                Masuk ke {{ $badge }}
            </button>
        </form>

        <div class="pt-4 border-t border-ink/15 space-y-2">
            <p class="text-[11px] font-bold uppercase tracking-wider text-ink/60">
                Buka Portal Lain di Tab Baru (Mandiri):
            </p>
            <div class="grid grid-cols-3 gap-2 text-center text-[11px] font-bold">
                <a href="{{ route('admin.login') }}" target="_blank" rel="noopener noreferrer"
                   class="px-2.5 py-2 rounded-xl border {{ $portal === 'admin' ? 'bg-ink text-paper border-ink' : 'bg-cream/80 text-ink border-ink/20 hover:bg-cream' }}">
                    Admin ↗
                </a>
                <a href="{{ route('kasir.login') }}" target="_blank" rel="noopener noreferrer"
                   class="px-2.5 py-2 rounded-xl border {{ $portal === 'kasir' ? 'bg-ink text-paper border-ink' : 'bg-cream/80 text-ink border-ink/20 hover:bg-cream' }}">
                    Kasir ↗
                </a>
                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
                   class="px-2.5 py-2 rounded-xl border {{ $portal === 'user' ? 'bg-ink text-paper border-ink' : 'bg-cream/80 text-ink border-ink/20 hover:bg-cream' }}">
                    User ↗
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
