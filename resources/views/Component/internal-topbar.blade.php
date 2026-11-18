<header class="bg-paper border-b border-ink/15 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="w-9 h-9 rounded-xl bg-ink text-paper flex items-center justify-center font-mono font-bold text-xs">
                GOR
            </a>
            <div>
                <span class="block text-xs font-extrabold tracking-tight text-ink">
                    {{ Auth::user()->role === 'admin' ? 'PANEL ADMINISTRATOR GOR' : 'PANEL KASIR OPERASIONAL' }}
                </span>
                <span class="block text-[10px] font-mono text-ink/65">
                    {{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})
                </span>
            </div>
        </div>

        @if(Auth::user()->role === 'admin')
            <nav class="hidden md:flex items-center gap-1.5">
                <a href="{{ route('admin.reports.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.reports.*') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream' }}">
                    Laporan Keuangan
                </a>
                <a href="{{ route('admin.fields.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.fields.*') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream' }}">
                    Kelola Lapangan
                </a>
                <a href="{{ route('admin.banners.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.banners.*') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream' }}">
                    Banner Event
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.users.*') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream' }}">
                    Akun Staf
                </a>
            </nav>
        @endif

        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg border border-ink/20 bg-cream/70 text-xs font-semibold text-ink">
                Lihat Web Publik
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold">
                    Keluar
                </button>
            </form>
        </div>
    </div>

    @if(Auth::user()->role === 'admin')
        <div class="md:hidden px-4 pb-3 flex flex-wrap gap-1.5 border-t border-ink/10 pt-2">
            <a href="{{ route('admin.reports.index') }}" class="px-2.5 py-1 rounded text-[11px] font-bold {{ request()->routeIs('admin.reports.*') ? 'bg-ink text-paper' : 'bg-cream text-ink' }}">Laporan</a>
            <a href="{{ route('admin.fields.index') }}" class="px-2.5 py-1 rounded text-[11px] font-bold {{ request()->routeIs('admin.fields.*') ? 'bg-ink text-paper' : 'bg-cream text-ink' }}">Lapangan</a>
            <a href="{{ route('admin.banners.index') }}" class="px-2.5 py-1 rounded text-[11px] font-bold {{ request()->routeIs('admin.banners.*') ? 'bg-ink text-paper' : 'bg-cream text-ink' }}">Event</a>
            <a href="{{ route('admin.users.index') }}" class="px-2.5 py-1 rounded text-[11px] font-bold {{ request()->routeIs('admin.users.*') ? 'bg-ink text-paper' : 'bg-cream text-ink' }}">Akun Staf</a>
        </div>
    @endif
</header>
