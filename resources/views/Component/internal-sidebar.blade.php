@php
    $isKasirPortal = request()->is('kasir*');
    $user = $isKasirPortal ? Auth::guard('kasir')->user() : Auth::guard('admin')->user();
    $isAdmin = ! $isKasirPortal && $user && $user->role === 'admin';
    $logoutRoute = $isKasirPortal ? route('kasir.logout') : route('admin.logout');
@endphp

<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed lg:sticky top-0 left-0 z-50 lg:z-30 w-64 shrink-0 h-dvh h-screen overflow-y-auto overscroll-contain bg-ink text-paper flex flex-col justify-between transition-transform duration-200 border-r border-paper/10">

    <div class="p-5 space-y-6">
        <div class="pb-5 border-b border-paper/15 flex items-start justify-between gap-2">
            <div>
                <span class="inline-block px-2.5 py-1 rounded bg-accent text-paper text-[10px] font-bold uppercase tracking-wider mb-2.5">
                    {{ $isAdmin ? 'PORTAL ADMIN GOR' : 'PORTAL KASIR GOR' }}
                </span>
                <h2 class="font-sans text-base font-extrabold text-paper tracking-tight leading-snug">
                    GOR Futsal Nusantara
                </h2>
                <p class="text-xs text-paper/65 mt-0.5">
                    Role: {{ $isAdmin ? 'Admin GOR' : 'Kasir GOR' }} • {{ $user?->name }}
                </p>
            </div>
            <button type="button" @click="sidebarOpen = false"
                    class="lg:hidden p-1.5 rounded-lg bg-paper/10 text-paper hover:bg-paper/20 text-xs font-bold">
                ✕
            </button>
        </div>

        <nav class="space-y-1.5">
            <span class="block px-3 text-[10px] font-bold uppercase tracking-wider text-paper/45 mb-2">
                MENU UTAMA
            </span>

            @if($isAdmin)
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <div class="py-1.5">
                    <hr class="border-paper/15">
                </div>

                <a href="{{ route('admin.reports.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.reports.*') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Keuangan</span>
                </a>

                <a href="{{ route('admin.operasional.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.operasional.*') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Operasional & Verified</span>
                </a>

                <a href="{{ route('admin.fields.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.fields.*') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Kelola Lapangan</span>
                </a>

                <a href="{{ route('admin.banners.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.banners.*') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Banner Event</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.users.*') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Akun Staff</span>
                </a>
            @else
                <a href="{{ route('kasir.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('kasir.dashboard') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Kasir & Verified Payment</span>
                </a>

                <a href="{{ route('kasir.reports.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('kasir.reports.*') ? 'bg-accent text-paper shadow-sm' : 'text-paper/80 hover:bg-paper/10 hover:text-paper' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Harian Kasir</span>
                </a>
            @endif
        </nav>

        <div class="p-3.5 rounded-xl bg-paper/5 border border-paper/15 text-[11px] leading-relaxed">
            <span class="block font-bold uppercase tracking-wider text-amber-300/90 mb-1">
                MULTI-SESI MANDIRI AKTIF
            </span>
            <p class="text-paper/70">
                Portal Admin, Kasir, dan User memiliki sesi terpisah. Perubahan di halaman ini tidak me-refresh tab portal lain.
            </p>
        </div>
    </div>

    <div class="p-5 border-t border-paper/15 space-y-2 shrink-0">
        @if($isAdmin)
            <a href="{{ route('kasir.login') }}" target="_blank" rel="noopener noreferrer"
               class="block w-full text-center px-3 py-2 rounded-xl bg-paper/10 hover:bg-paper/15 text-paper text-xs font-semibold transition">
                Buka Portal Kasir ↗
            </a>
        @else
            <a href="{{ route('admin.login') }}" target="_blank" rel="noopener noreferrer"
               class="block w-full text-center px-3 py-2 rounded-xl bg-paper/10 hover:bg-paper/15 text-paper text-xs font-semibold transition">
                Buka Portal Admin ↗
            </a>
        @endif

        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
           class="block w-full text-center px-3 py-2 rounded-xl bg-paper/10 hover:bg-paper/15 text-paper text-xs font-semibold transition">
            Buka Web User / Customer ↗
        </a>

        <form method="POST" action="{{ $logoutRoute }}">
            @csrf
            <button type="submit"
                    class="w-full px-3 py-2 rounded-xl bg-rose-700/90 hover:bg-rose-700 text-white text-xs font-bold transition">
                Keluar Sesi {{ $isAdmin ? 'Admin' : 'Kasir' }}
            </button>
        </form>
    </div>
</aside>
